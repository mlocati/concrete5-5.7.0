<?php
namespace Concrete\Controller\Element\Notification;

use Concrete\Core\Controller\ElementController;
use Concrete\Core\Notification\View\StandardListViewInterface;

/**
 * @since 8.0.0
 */
class Menu extends ListDetails
{

    public function getElement()
    {
        return 'notification/menu';
    }

    public function view()
    {
        parent::view();
    }
}
