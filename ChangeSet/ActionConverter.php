<?php

namespace CommerceToolsExporter\ChangeSet;

use CommerceToolsExporter\ChangeSet\Action\Collection;
use CommerceToolsExporter\ChangeSet\Exception\ChangeSetConverterException;

class ActionConverter
{
    /**
     * @param Change $change
     * @param Collection $actions
     * 
     * @return array
     * @throws ChangeSetConverterException
     */
    public function convert(Change $change, Collection $actions)
    {
        $changes = [];
        foreach(array_merge($change->getChanged(), $change->getAdded()) as $field => $value) {
            if(null === $action = $actions->get($field)) {
                throw new ChangeSetConverterException('Invalid field map found for '. $field);
            }

            $changes[] = $action->getActionValue($value);
        }
        
        return $changes;
    }
}