<?php  defined('C5_EXECUTE') or die("Access Denied."); ?>

<div class="ccm-block-topic-list-flat-filter">
<?
$node = $tree->getRootTreeNodeObject();
if (is_object($node)) {
    $node->populateDirectChildrenOnly(); ?>
    <ol class="breadcrumb">
    <? foreach($node->getChildNodes() as $child) { ?>
        <li><a href="<?=$view->controller->getTopicLink($child)?>"
                <? if (isset($selectedTopicID) && $selectedTopicID == $topic->getTreeNodeID()) { ?>
                    class="ccm-block-topic-list-topic-selected active"
                <? } ?> ><?=$child->getTreeNodeDisplayName()?></a></li>
    <? } ?>
    </ol>
<? } ?>
</div>