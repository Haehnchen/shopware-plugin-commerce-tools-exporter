<?php

namespace CommerceToolsExporter\ChangeSet\Action;

interface ActionInterface
{
    /**
     * @return string
     */
    public function getField();

    /**
     * @return string
     */
    public function getAction();

    /**
     * @param mixed $value
     * @return array
     */
    public function getActionValue($value);
}