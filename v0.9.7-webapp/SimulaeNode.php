<?php 

class SimulaeNode{

    /* SimulaeNode

The SimulaeNode class is a php port of SimulaeObject notation, a object structure designed to codify any entity, regardless of corporeality (whether or not it physically exists).

Structure:
    id          : the node 'id' is a unique string identifier that is used as a key for fast-access and reference for other nodes, optimizing Scalability. Hash-key lookups are significantly faster than indexed arrays, which is optimal for large-datasets like complex worlds. 
    nodetype    : a string identifier codifying what category of entity the node is structured as. 
        FAC -> Faction.
        POI -> Person-Of-Interest.
        PTY -> Party. a non-faction collection of individuals, which can be generic or specific, depending on membership specifics. Technically unecessary in a finished product as it would be better to 
        LOC -> Location. A place, area, room, or collection of rooms (commonly known as buildings or structures).
        OBJ -> Object. Any non-living entity.

        Now you may ask, well if the SimulaeNode object structure is designed to codify any type of entity, regardless of category, why would you add nodetype to recategorize them all? 
            Well, quite simply, people have difficulty designing systems in which things are the same, but also different. The point of SimulaeNode is that reality bending concepts like "picking up a room, and putting it in your pocket" or ""
    references  : 
    attributes  : numeric values, integer and decimal
    relations   : opinion of other related nodes on several factors
        Policy          : ideological differences
        Reputation      : both positive and negative reputation scores
            Two values allows for 2 dimensional opinions (Inspired by the reputation system from Fallout:New Vegas):
                High positive rep.  -> Good / Liked / Loved
                High negative rep.  -> Bad / Disliked / Hated
                Both high reps.     -> Chaotic / Unsure
        Interactions    : number of interactions between the two nodes
        Disposition     : A more discrete codification of the relationship between the two nodes
            neutral     | this node is non-hostile to other node
            friendly    | this node is friendly to other node
            hostile     | this node is hostile to other node
            affiliate   | same group/faction
            master      | other node is subservient under this node
            servant     | other node has mastership over this node
            captor      | this node is detained by node
            captured    | this node has detained node
    checks      : boolean (true/false) values
    policies    : collection of ideological opinions/beliefs and weights
        The beliefs are accompanied by weights, which signify the importance of that belief to the entity. The higher the value (closer to 1) the more important the belief is to the entity.
    abilities   : available actions of this node
            structure TBD

    */

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

        # if ($this->nodetype == "FAC" ) { echo $this->attributes["capability_points"]."\n"; }
        
    }

    function summary( SimulaeNode $perspective=null ){

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." summary()}\n"; }

        /* Simple single-line string summary of the node's important details  
                contents vary by nodetype

            $perspective - the SimulaeNode through which the node is observed. As a rival faction may observe an entity to be hostile, or a passive POI may observe it to be friendly/non-hostile.
        */

        # No given perspective.
        if( is_null($perspective) ){  

            return strval($this->nodetype) . " " . strval($this->get_reference("name"));

        }elseif( $perspective->get_id() == $this->get_id() ){

            $relation = $this->get_relation($perspective->get_id(), $perspective->get_nodetype());

            # location nodetype specific output
            if($this->nodetype == "LOC"){
                return "<" . strval($relation['disposition']) . "> [".$this->get_attribute("defense")."] ". strval($this->nodetype) . " " . strval($this->get_reference("name"));
            }elseif ($this->nodetype == "FAC") {
                return "<" . strval($relation['disposition']) . "> "
                    . strval($this->nodetype) . " " 
                    . strval($this->get_reference("name")) . " [" 
                    . strval($this->attributes["capability_points"]) . "]";
            }else{

                return "<" . strval($relation['disposition']) . "> " . strval($this->nodetype) . " " . strval($this->get_reference("name"));
            }

        }else{

            $relation = $this->get_relation($perspective->get_id(), $perspective->get_nodetype());

            # location nodetype specific output
            if($this->nodetype == "LOC"){
                return "<" . strval($relation['disposition']) . "> [".$this->get_attribute("defense")."] ". strval($this->nodetype) . " " . strval($this->get_reference("name"));
            }

            return "<" . strval($relation['disposition']) . "> " . strval($this->nodetype) . " " . strval($this->get_reference("name"));
        }
    }


    ### Getter/Setters ###

    function get_id(){
        # self-evident function #

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." get_id()}\n"; }

        return $this->id;
    }

    function get_nodetype(){
        # self-evident function #

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." get_nodetype()}\n"; }

        return $this->nodetype;
    }

    
    /* References : associative array functions 
        
    */

    function get_references(){
        /* returns the node's references */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." get_references()}\n"; }
        
        return clone $this->references;
    }
    function get_reference( string $key ){
        /* using a string key identifier, returns the corresponding value from the node's references, provided that the key/value pair exists */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." get_reference($key)}\n"; }
        
        return array_key_exists($key, $this->references) ? $this->references[$key] : null;
    }
    function delete_reference( string $key ){
        /* removes the entry provided that the k,v pair exists */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." delete_reference($key)}\n"; }
        
        if( array_key_exists($key, $this->references)){
            unset($this->references[$key]);
        }
    }
    function set_reference( string $key, string $value ){
        /* modifies the value for a given key */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." set_reference($key, & $value)}\n"; }
        
        $this->references[$key] = $value;
    }

    /* Attributes : associative array functions 

    */

    function get_attributes(){
        /* returns the node's attributes */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." get_attributes()}\n"; }
        
        return clone $this->attributes;
    }
    function get_attribute( string $key ){
        /* using a string key identifier, returns the corresponding value from the node's attributes, provided that the key/value pair exists */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." set_attribute($key, ...)}\n"; }
        
        return array_key_exists($key, $this->attributes) ? $this->attributes[$key] : null;
    }
    function delete_attribute( string $key ){
        /* removes the entry provided that the k,v pair exists */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." delete_attribute($key, ...)}\n"; }
        
        if( array_key_exists($key, $this->attributes)){
            unset($this->attributes[$key]);
        }
    }
    function set_attribute( string $key, mixed $value ){
        /* modifies the value for a given key */

        
        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." set_attribute($key, ...)}\n"; }
        
        
        $this->attributes[$key] = $value;
    }


    /* Relations : associative array functions 

        Structure
            "FAC" => [ "id" => <SimulaeNode>, ... ]
            "POI" => [ "id" => <SimulaeNode>, ... ]
            "PTY" => [ "id" => <SimulaeNode>, ... ]

            "OBJ" => [ "id" => <SimulaeNode>, ... ]
            "LOC" => [ "id" => <SimulaeNode>, ... ]

    */

    function get_relations(){
        /* returns the node's relations */

        
        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." get_relations()}\n"; }
        
        
        return $this->relations;
    }

    function has_relation( string $key ){

        
        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." has_relation($key)}\n"; }
        

        return array_key_exists($key, $this->get_relations());

    }


    function get_relation( string $key, string $key_type ){
        /* using a string key identifier, returns the corresponding value from the node's relations, provided that the key/value pair exists 

            This function requires both the node's id as well as its nodetype
        */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." get_relation($key, $key_type)}\n"; }

        #if( array_key_exists( $key_type, $this->relations ) )

        # account for self-referencing $key
        if($this->id == $key){
            return [
                "nodetype" => $this->nodetype,
                "policy" => [],
                "reputation" => [0,0],
                "interractions" => 0,
                "disposition" => "actor"
            ];
        }

        # if key/value pair not found, this function will attempt to automatically generate a relation between this and the $key node
        return array_key_exists( $key, $this->relations[$key_type] ) ? $this->relations[$key_type][$key] : $this->update_relation($GLOBALS['ngin']->state->$key_type[$key] );
    }


    function delete_relation( string $key, string $key_type ){
        /* removes the entry provided that the k,v pair exists */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." delete_relation($key, $key_type)}\n"; }
        
        if( array_key_exists($key, $this->relations[$key_type])){
            unset($this->relations[$key_type][$key]);
        }
    }


    function update_relation( SimulaeNode $node, int $rep_diff = 0 ){

        /* evaluates the policy differential between this node and $node, then modifies/adds the entry using set_relation()
        */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." update_relation(".$node->get_id().")}\n"; }

        if($this->id == $node->get_id()){
            return;
        }

        list($diff_score, $diff) = $this->policy_diff($node->get_policies(), $summary=true);

        $relation = $this->has_relation( $node->get_id() ) ? 
            [           # has previous relation
                "nodetype" => $node->get_nodetype(),
                "policy" => $diff,
                "reputation" => 
                    $this->get_relation($node->get_id())["reputation"],
                "interractions" => 1,
                "disposition" => $disposition
            ] : [       # no previous relation
                "nodetype" => $node->get_nodetype(),
                "policy" => $diff,
                "reputation" => [0,0],
                "interractions" => 1,
                "disposition" => $this->get_disposition($diff_score, 0, 0)
            ];

        if ($rep_diff >= 0) {
            $relation["reputation"][0] += $rep_diff;
        }else{ # -rep_diff
            $relation["reputation"][1] += abs($rep_diff);
        }

        $disposition = $this->check('has_master') ? 
            $this->get_master()->get_relation(
                    $node->get_id(), 
                    $node->get_nodetype())['disposition'] : 
            $this->get_disposition( 
                $diff_score, 
                $relation["reputation"][0], 
                $relation["reputation"][1]);

        return $this->set_relation( $node->get_id(), $node->get_nodetype(), $relation );

    }


    function set_relation( string $key, string $key_type, array $values ){
        /*  modifies the value of the relation structure for a given node
                To ensure no malformed structures (that the minimum necessary data is included), k/v pairs from $values are added/set in $relation.
        */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." set_relation($key, $key_type, ...)}\n"; }

        # minimum structure requirements
        $relation = [
            "nodetype" => $key_type,
            "policy" => [],
            "reputation" => [0,0],
            "interractions" => 1,
            "disposition" => null
        ];

        foreach ($values as $factor => $val ) {
            $relation[$factor] = $val;
        }

        $this->relations[$key_type][$key] = $relation;

        return $relation;
    }


    function set_master( $new_master ){
        /* set master-ship of this node to $new_master */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." set_master(".$new_master->get_id().")}\n"; }
        
        if( in_array($this->nodetype, ["OBJ","LOC"]) ){

            $this->set_relation( $new_master->get_id(), $new_master->get_nodetype(), [ "policy" => $new_master->get_policies(),"disposition" => "master" ] );

        }elseif ( in_array($this->nodetype, ["PTY","POI"]) ) {
            $this->set_relation( $new_master->get_id(), $new_master->get_nodetype(), [ "disposition" => "master" ] );
        }

    }


    function get_master(){
        /* if it exists, yield this node's referenced 'master' node */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." get_master()}\n"; }
        

        if($this->check("has_master")){
            $master_id = $this->get_reference("master");
            foreach($this->relations as $nodetype => $nodes){
                if( array_key_exists($master_id, $nodes) ){
                    return $GLOBALS['ngin']->state->$nodetype[$master_id];
                }
            }

            throw new Exception($this->id . " get_master() ".$master_id." not found in relations", 1);
            
        }
        return null;

    }
    function get_membership(){

    }


    /* Checks : associative array functions 

    */

    function get_checks(){
        /* returns the node's checks */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." get_checks()}\n"; }
        
        return clone $this->checks;
    }
    function check( string $check ){
        /* using a string key identifier, returns the corresponding value from the node's attributes, provided that the key/value pair exists */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." check($check)}\n"; }
        
        return array_key_exists($check, $this->checks) ? $this->checks[$check] : null;
    }
    function delete_check( string $key ){
        /* removes the entry provided that the k,v pair exists */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." delete_check($key)}\n"; }
        
        if( array_key_exists($key, $this->checks)){
            unset($this->checks[$key]);
        }
    }
    function set_check( string $key, bool $value ){
        /* modifies the value for a given key */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." set_check($key, $value)}\n"; }
        
        $this->checks[$key] = $value;
    }


    /* Policy : associative array functions 

    */

    function get_policies(){
        /* returns the node's policies */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." get_policies()}\n"; }
        
        # if this node has no defined policies...
        if($this->policies == []){
            # but has a 'master' node:
            if($this->check('has_master')){
                # use the 'master's policies in this one's place
                return $this->get_master()->get_policies();

                #$this->policies = $this->get_master()->get_policies();
            }
        }

        return $this->policies;
    }


    function get_policy( string $policy ){
        /* using a string key identifier, returns the corresponding value from the node's policies, provided that the key/value pair exists */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." get_policy($policy)}\n"; }
        
        return array_key_exists($policy, $this->policies) ? $this->policies[$policy] : null ;
    }


    function delete_policy( string $key ){
        /* removes the entry provided that the k,v pair exists */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." delete_policy($key)}\n"; }
        
        if( array_key_exists($key, $this->policies)){
            unset($this->policies[$key]);
        }
    }


    function set_policy( string $key, bool $value ){
        /* modifies the value for a given key */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." set_policy($key, $value)}\n"; }
        
        $this->policies[$key] = $value;
    }

    # Policy specific functions

    function policy_diff( array $comparison_policy, bool $summary = false){

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." policy_diff(...)}\n"; }
        
        $diff_summary = [];
        $diff = 0;

        foreach( $this->get_policies() as $factor => $policy ){

            $delta = abs( $this->get_policy_index( $factor, $policy[0] ) - $this->get_policy_index( $factor, strval($comparison_policy[$factor][0]) ) );

            if($summary){
                $diff_summary[$factor] = [["Agreement", "Civil", "Contentious",  "Opposition", "Diametrically Opposed"][$delta], $delta];
            }
            $diff += $delta;
        }


        if($summary){
            return [$diff, $diff_summary];
        }
        return $diff;

    }

    function get_policy_index( string $factor, $policy ){
        /* Returns integer position of a given policy factor on its discrete spectrum
        */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." get_policy_index($factor, ...)}\n"; }
        
        return array_search($policy, [
            "Economy" => ["Communist", "Socialist", "Indifferent", "Capitalist", "Free-Capitalist"],
            "Liberty" => ["Authoritarian", "Statist", "Indifferent", "Libertarian", "Anarchist"],
            "Culture" => ["Traditionalist", "Conservative", "Indifferent", "Progressive", "Accelerationist"],
            "Diplomacy" => ["Globalist", "Diplomatic", "Indifferent", "Patriotic", "Nationalist"],
            "Militancy" => ["Militarist", "Strategic", "Indifferent", "Diplomatic", "Pacifist"],
            "Diversity" => ["Homogenous", "Preservationist", "Indifferent", "Heterogeneous", "Multiculturalist"],
            "Secularity" => ["Apostate", "Secularist", "Indifferent", "Religious", "Devout"],
            "Justice" => ["Retributionist", "Punitive", "Indifferent", "Correctivist", "Rehabilitative"],
            "Naturalism" => [ "Ecologist", "Naturalist", "Indifferent", "Productivist", "Industrialist"],
            "Government" => ["Democratic", "Republican", "Indifferent", "Oligarchic", "Autocratic"]
        ][$factor] );

    }

    function get_disposition( int $diff_score, int $positive_rep, int $negative_rep ){
        /* Depending on the total difference between two entity's policies, they will default treat each other differently.
            These magic-number values will be tweaked in further versions for balance.
        */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." get_disposition($diff_score, $positive_rep, $negative_rep)}\n"; }
        
        if( $diff_score <= 5 ){
            return "friendly";
        }elseif($diff_score <= 15 ){
            return "neutral";
        }elseif($diff_score > 15 ){
            return "hostile";
        }

    }


    /* Abilities : associative array functions 

    */

    function get_abilities(){
        /* returns the node's abilities */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." get_abilities()}\n"; }
        
        return clone $this->abilities;
    }
    function get_ability( string $ability ){
        /* using a string key identifier, returns the corresponding value from the node's attributes, provided that the key/value pair exists */

        if($GLOBALS["DEBUG"]){echo "DEBUG{SimulaeNode ".$this->id." get_ability($ability)}\n"; }

        return array_key_exists($ability, $this->abilities) ? $this->abilities[$ability] : null;
    }
    function delete_ability( string $key ){
        /* removes the entry provided that the k,v pair exists */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." delete_ability($key)}\n"; }

        if( array_key_exists($key, $this->abilities)){
            unset($this->abilities[$key]);
        }
    }
    function set_ability( string $key, array $value ){
        /* modifies the value for a given key */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeNode ".$this->id." set_ability($key, ...)}\n"; }

        $this->abilities[$key] = $value;
    }

}



class SimulaeState{

    /* SimulaeState
            Serves as the SimulaeNode index, partitioned by nodetypes. 
            Also keeps track of the actor node, which is operated by the player.
    */

    public $FAC;
    public $POI;
    public $PTY;
    public $OBJ;
    public $LOC;

    public $actor;

    function __construct( $FAC, $POI, $PTY, $OBJ, $LOC ){

        $this->FAC = $FAC;
        $this->POI = $POI;
        $this->PTY = $PTY;
        $this->OBJ = $OBJ;
        $this->LOC = $LOC;

        $this->actor = null;

    }

    function set_actor(){
        /* User may choose their avatar node */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeState set_actor()}\n"; }

        $this->actor = random_choice($this->FAC);

        #$this->actor = $GLOBALS["ngin"]->user_choice_array( "User choose avatar node:", array_values($this->FAC), $random_opt=false, $simulaenode_options=true );

    }

    function get_actor(){
        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeState get_actor()}\n"; }

        return $this->actor;

    }


    function get_nodes( string $nodetype ){

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeState get_nodes()}\n"; }

        if( $nodetype == "FAC" ){
            return $this->FAC; 

        }elseif ($nodetype == "POI") {
            return $this->POI;

        }elseif ($nodetype == "PTY") {
            return $this->PTY;

        }elseif ($nodetype == "OBJ") {
            return $this->OBJ;

        }elseif ($nodetype == "LOC") {
            return $this->LOC;
        }

    }


    function get_all_nodes(){

        if($GLOBALS["DEBUG"]){ echo "DEBUG{SimulaeState get_all_nodes()}\n"; }
        /* variable dump as associative array for php->json conversion */

        return [    "FAC"=>$this->FAC,
                    "POI"=>$this->POI,
                    "PTY"=>$this->PTY,
                    "OBJ"=>$this->OBJ,
                    "LOC"=>$this->LOC
                ];
    }

}

?>