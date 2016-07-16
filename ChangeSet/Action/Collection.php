<?php

namespace CommerceToolsExporter\ChangeSet\Action;

class Collection
{
    /**
     * @var ActionInterface[]
     */
    private $actions;

    /**
     * @var array<string>
     */
    private $fields;

    public function __construct(array $action = [])
    {
        $this->actions = $action;
    }

    /**
     * @param ActionInterface $action
     */
    public function add(ActionInterface $action)
    {
        $this->actions[] = $action;
    }
    
    /**
     * @return ActionInterface[]
     */
    public function fields()
    {
        if(is_array($this->fields)) {
           return $this->fields; 
        }
        
        return $this->fields = array_map(function(ActionInterface $field) {
            return $field->getField();
        }, $this->actions);
    }
    
    /**
     * @return ActionInterface[]
     */
    public function all()
    {
        return $this->actions;
    }

    /**
     * @param string $field
     * @return ActionInterface
     */
    public function get($field)
    {
        foreach($this->actions as $action) {
            if($action->getField() == $field) {
                return $action;
            }
        }
        
        return null;
    }
}