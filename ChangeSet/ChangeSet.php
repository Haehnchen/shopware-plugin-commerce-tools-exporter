<?php

namespace CommerceToolsExporter\ChangeSet;

class ChangeSet
{
    /**
     * @var array
     */
    private $old;

    /**
     * @var array
     */
    private $new;

    /**
     * @var array
     */
    private $managedFields;

    public function __construct(array $old, array $new, array $managedFields)
    {
        $this->old = array_intersect_key($old, array_fill_keys($managedFields, 1));
        $this->new = array_intersect_key($new, array_fill_keys($managedFields, 1));
        $this->managedFields = $managedFields;
    }

    public function compute()
    {
        $added = [];
        $changed = [];
        $deleted = [];
        
        foreach($this->managedFields as $field) {
            
            // new field added
            if(array_key_exists($field, $this->new) && !array_key_exists($field, $this->old)) {
                $added[$field] = $this->new[$field];
                continue;
            }

            // deleted field
            if(array_key_exists($field, $this->old) && !array_key_exists($field, $this->new)) {
                $deleted[$field] = $this->old[$field];
                continue;
            }

            // change field
            if(array_key_exists($field, $this->old) && array_key_exists($field, $this->new)) {
                if($this->old[$field] !== $this->new[$field]) {
                    $changed[$field] = $this->new[$field];
                }
                continue;
            }
        }

        return new Change($added, $changed, $deleted);
    }
}