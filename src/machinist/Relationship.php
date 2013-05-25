<?php
namespace Machinist;

use Machinist\Blueprint;

class Relationship
{
    private $local_column;
    private $foreign_column;
    private $blueprint;

    public function __construct(Blueprint $blueprint)
    {
        $this->blueprint = $blueprint;
    }

    public function local($key)
    {
        $this->local_column = $key;
        return $this;
    }

    public function foreign($key)
    {
        $this->foreign_column = $key;
        return $this;
    }

    public function getLocal()
    {
        return $this->local_column;
    }

    public function getForeign()
    {
        return $this->foreign_column;
    }

    public function getBlueprint()
    {
        return $this->blueprint;
    }

}
