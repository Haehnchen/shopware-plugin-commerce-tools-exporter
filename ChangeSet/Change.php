<?php

namespace CommerceToolsExporter\ChangeSet;

class Change
{
    /**
     * @var array
     */
    private $added;
    
    /**
     * @var array
     */
    private $changed;
    
    /**
     * @var array
     */
    private $deleted;

    public function __construct(array $added, array $changed, array $deleted)
    {
        $this->added = $added;
        $this->changed = $changed;
        $this->deleted = $deleted;
    }

    /**
     * @return array
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * @return array
     */
    public function getChanged()
    {
        return $this->changed;
    }

    /**
     * @return array
     */
    public function getDeleted()
    {
        return $this->deleted;
    }
}