<?php

namespace Concrete\Core\Http;

use Concrete\Flysystem\File;
use Concrete\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Heavily based on BinaryFileResponse represents an HTTP response delivering a file.
 *
 * @author Korvin Szanto <korvinszanto@gmail.com>
 * @author Niklas Fiekas <niklas.fiekas@tu-clausthal.de>
 * @author stealth35 <stealth35-php@live.fr>
 * @author Igor Wiedler <igor@wiedler.ch>
 * @author Jordan Alliot <jordan.alliot@gmail.com>
 * @author Sergey Linnik <linniksa@gmail.com>
 */
class FlysystemFileResponse extends Response
{
    protected static $trustXSendfileTypeHeader = false;

    /** @type File */
    protected $file;

    /** @type FilesystemInterface */
    protected $filesystem;

    protected $offset;
    protected $maxlen;

    /**
     * Constructor.
     *
     * @param File                $file               The file to stream
     * @param FilesystemInterface $filesystem         The filesystem instance to get info with
     * @param int                 $status             The response status code
     * @param array               $headers            An array of response headers
     * @param bool                $public             Files are public by default
     * @param null|string         $contentDisposition The type of Content-Disposition to set automatically with the filename
     * @param bool                $autoEtag           Whether the ETag header should be automatically set
     */
    public function __construct($file, FilesystemInterface $filesystem, $status = 200, $headers = array(), $public = true, $contentDisposition = null, $autoEtag = false)
    {
        parent::__construct(null, $status, $headers);

        $this->filesystem = $filesystem;
        $this->setFile($file, $contentDisposition, $autoEtag);

        if ($public) {
            $this->setPublic();
        }
    }

    /**
     * @param \SplFileInfo|string $file               The file to stream
     * @param int                 $status             The response status code
     * @param array               $headers            An array of response headers
     * @param bool                $public             Files are public by default
     * @param null|string         $contentDisposition The type of Content-Disposition to set automatically with the filename
     * @param bool                $autoEtag           Whether the ETag header should be automatically set
     *
     * @return BinaryFileResponse The created response
     */
    public static function create($file = null, $status = 200, $headers = array(), $public = true, $contentDisposition = null, $autoEtag = false)
    {
        return new static($file, $status, $headers, $public, $contentDisposition, $autoEtag);
    }

    /**
     * Sets the file to stream.
     *
     * @param \SplFileInfo|string $file               The file to stream
     * @param string              $contentDisposition
     * @param bool                $autoEtag
     *
     * @return BinaryFileResponse
     *
     * @throws FileException
     */
    public function setFile($file, $contentDisposition = null, $autoEtag = false)
    {
        if ($file instanceof File) {
            $file->setFilesystem($this->filesystem);
        } else {
            $file = $this->filesystem->get($file);
        }

        if (!$this->filesystem->has($file->getPath())) {
            throw new FileException('File must be readable.');
        }

        $this->file = $file;

        if ($autoEtag) {
            $this->setAutoEtag();
        }

        if ($contentDisposition) {
            $this->setContentDisposition($contentDisposition);
        }

        return $this;
    }

    /**
     * Gets the file.
     *
     * @return File The file to stream
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Automatically sets the ETag header according to the checksum of the file.
     */
    public function setAutoEtag()
    {
        $this->setEtag(sha1_file($this->file->getPath()));

        return $this;
    }

    /**
     * Sets the Content-Disposition header with the given filename.
     *
     * @param string $disposition      ResponseHeaderBag::DISPOSITION_INLINE or ResponseHeaderBag::DISPOSITION_ATTACHMENT
     * @param string $filename         Optionally use this filename instead of the real name of the file
     * @param string $filenameFallback A fallback filename, containing only ASCII characters. Defaults to an automatically encoded filename
     *
     * @return BinaryFileResponse
     */
    public function setContentDisposition($disposition, $filename = '', $filenameFallback = '')
    {
        if ($filename === '') {
            $filename = basename($this->file->getPath());
        }

        $dispositionHeader = $this->headers->makeDisposition($disposition, $filename, $filenameFallback);
        $this->headers->set('Content-Disposition', $dispositionHeader);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(\Symfony\Component\HttpFoundation\Request $request)
    {
        $this->headers->set('Content-Length', $this->file->getSize());

        if (!$this->headers->has('Accept-Ranges')) {
            // Only accept ranges on safe HTTP methods
            $this->headers->set('Accept-Ranges', $request->isMethodSafe() ? 'bytes' : 'none');
        }

        if (!$this->headers->has('Content-Type')) {
            $this->headers->set('Content-Type', $this->file->getMimetype() ?: 'application/octet-stream');
        }

        if ('HTTP/1.0' != $request->server->get('SERVER_PROTOCOL')) {
            $this->setProtocolVersion('1.1');
        }

        $this->ensureIEOverSSLCompatibility($request);

        $this->offset = 0;
        $this->maxlen = -1;

        if ($request->headers->has('Range')) {
            // Process the range headers.
            if (!$request->headers->has('If-Range') || $this->getEtag() == $request->headers->get('If-Range')) {
                $range = $request->headers->get('Range');
                $fileSize = $this->file->getSize();

                list($start, $end) = explode('-', substr($range, 6), 2) + array(0);

                $end = ('' === $end) ? $fileSize - 1 : (int) $end;

                if ('' === $start) {
                    $start = $fileSize - $end;
                    $end = $fileSize - 1;
                } else {
                    $start = (int) $start;
                }

                if ($start <= $end) {
                    if ($start < 0 || $end > $fileSize - 1) {
                        $this->setStatusCode(416);
                    } elseif ($start !== 0 || $end !== $fileSize - 1) {
                        $this->maxlen = $end < $fileSize ? $end - $start + 1 : -1;
                        $this->offset = $start;

                        $this->setStatusCode(206);
                        $this->headers->set('Content-Range', sprintf('bytes %s-%s/%s', $start, $end, $fileSize));
                        $this->headers->set('Content-Length', $end - $start + 1);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Sends the file.
     */
    public function sendContent()
    {
        if (!$this->isSuccessful()) {
            parent::sendContent();

            return;
        }

        if (0 === $this->maxlen) {
            return;
        }

        $out = fopen('php://output', 'wb');
        $file = $this->filesystem->readStream($this->file->getPath());
        stream_copy_to_stream($file, $out, $this->maxlen, $this->offset);


        fclose($out);
        fclose($file);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException when the content is not null
     */
    public function setContent($content)
    {
        if (null !== $content) {
            throw new \LogicException('The content cannot be set on a BinaryFileResponse instance.');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return false
     */
    public function getContent()
    {
        return false;
    }

    /**
     * Trust X-Sendfile-Type header.
     */
    public static function trustXSendfileTypeHeader()
    {
        self::$trustXSendfileTypeHeader = true;
    }
}
