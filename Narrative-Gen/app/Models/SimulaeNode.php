<?php

	namespace App\Models;

	class SimulaeNode extends Model{

		public $id;
	    public $nodetype;
	    public $references;
	    public $attributes;
	    public $relations;
	    public $checks;
	    public $policies;
	    public $abilities;

	    function __construct(   $id,
	                            $nodetype,
	                            $references,
	                            $attributes,
	                            $relations,
	                            $checks,
	                            $policies,
	                            $abilities
	                        ){

	        $this->id = $id;
	        $this->nodetype = $nodetype;
	        $this->references = $references;
	        $this->attributes = $attributes;
	        $this->relations = $relations;
	        $this->checks = $checks;
	        $this->policies = $policies;
	        $this->abilities = $abilities;

	    }


	}


?>