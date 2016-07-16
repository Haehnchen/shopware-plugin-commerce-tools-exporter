<?php

namespace CommerceToolsExporter\ChangeSet\Action;

class Action implements ActionInterface
{
    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $action;

    public function __construct($field, $action)
    {
        $this->field = $field;
        $this->action = $action;
    }

    /**
     * {@inheritdoc}
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * {@inheritdoc}
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * {@inheritdoc}
     */
    public function getActionValue($value)
    {
        return [
            'action' => $this->getAction(),
            $this->field => $value,
        ];
    }
}