
<?php

$ngin;

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

        /* 'Introduce' all factions to each other.
                functionally necessary until factions can dynamically interact with each other autonomously.
        */

        foreach( $this->FAC as $fac_id => $fac){
            foreach( $this->FAC as $fac_id2 => $fac2){
                
                if($fac_id != $fac_id2){

                    $fac->update_relation($fac2);
                    $fac2->update_relation($fac);

                }

            }    
        }

        $this->set_actor();

    }

    function set_actor(){
        /*  */

        #$this->actor = $this->FAC[ $GLOBALS['ngin']->user_choice_preset( $this->FAC, $random_opt=true ) ];
    
    }


    function get_nodes( string $nodetype ){

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
        /* variable dump as associative array for php->json conversion */

        return [    "FAC"=>$this->FAC,
                    "POI"=>$this->POI,
                    "PTY"=>$this->PTY,
                    "OBJ"=>$this->OBJ,
                    "LOC"=>$this->LOC
                ];
    }

}


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
                        affiliated  | same group/faction
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
        $this->attributes = ($attributes);
        $this->relations = ($relations);
        $this->checks = ($checks);
        $this->policies = ($policies);
        $this->abilities = ($abilities);
        
    }

    function summary( SimulaeNode $perspective=null ){

        /* Simple single-line string summary of the node's important details  
                contents vary by nodetype

            $perspective - the SimulaeNode through which the node is observed. As a rival faction may observe an entity to be hostile, or a passive POI may observe it to be friendly/non-hostile.
        */

        # No given perspective.
        if( is_null($perspective) ){  

            return strval($this->nodetype) . " " . strval($this->get_reference("name"));

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
        return $this->id;
    }

    function get_nodetype(){
        # self-evident function #
        return $this->nodetype;
    }

    
    /* References : associative array functions 
        
    */

    function get_references(){
        /* returns the node's references */
        return clone $this->references;
    }
    function get_reference( string $reference ){
        /* using a string key identifier, returns the corresponding value from the node's references, provided that the key/value pair exists */
        return array_key_exists($reference, $this->references) ? $this->references[$reference] : null;
    }
    function delete_reference( string $key ){
        /* removes the entry provided that the k,v pair exists */
        if( array_key_exists($key, $this->references)){
            unset($this->references[$key]);
        }
    }
    function set_reference( string $key, string $value ){
        /* modifies the value for a given key */
        $this->references[$key] = $value;
    }

    /* Attributes : associative array functions 

    */

    function get_attributes(){
        /* returns the node's attributes */
        return clone $this->attributes;
    }
    function get_attribute( string $attribute ){
        /* using a string key identifier, returns the corresponding value from the node's attributes, provided that the key/value pair exists */
        return array_key_exists($attribute, $this->attributes) ? $this->attributes[$attribute] : null;
    }
    function delete_attribute( string $key ){
        /* removes the entry provided that the k,v pair exists */
        if( array_key_exists($key, $this->attributes)){
            unset($this->attributes[$key]);
        }
    }
    function set_attribute( string $key, mixed $value ){
        /* modifies the value for a given key */
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
        return clone $this->relations;
    }


    function get_relation( string $key, string $key_type ){
        /* using a string key identifier, returns the corresponding value from the node's relations, provided that the key/value pair exists 

            This function requires both the node's id as well as its nodetype
        */

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
        if( array_key_exists($key, $this->relations[$key_type])){
            unset($this->relations[$key_type][$key]);
        }
    }


    function update_relation( SimulaeNode $node ){

        /* evaluates the policy differential between this node and $node, then modifies/adds the entry using set_relation()
        */

        if($this->id == $node->get_id()){
            return;
        }

        list($diff_score, $diff) = $this->policy_diff($node->get_policies(), $summary=true);

        #echo $diff_score."\n";

        $relation = [
            "nodetype" => $node->get_nodetype(),
            "policy" => $diff,
            "reputation" => [0,0],
            "interractions" => 1,
            "disposition" => $this->get_disposition_from_score($diff_score)
        ];

        #echo $this->id." updated relation with ".$node->get_id()." -> ".$relation['disposition']."\n";

        return $this->set_relation( $node->get_id(), $node->get_nodetype(), $relation );

    }


    function set_relation( string $key, string $key_type, array $values ){
        /*  modifies the value of the relation structure for a given node
                To ensure no malformed structures (that the minimum necessary data is included), k/v pairs from $values are added/set in $relation.
        */

        # minimum structure requirements
        $relation = [
            "nodetype" => $key_type,
            "policy" => [],
            "reputation" => [0,0],
            "interractions" => 1,
            "disposition" => "neutral"
        ];

        foreach ($values as $factor => $val ) {
            $relation[$factor] = $val;
        }

        $this->relations[$key_type][$key] = $relation;

        return $relation;
    }


    function set_master( $new_master ){
        /* set master-ship of this node to $new_master */

        if( in_array($this->nodetype, ["OBJ","LOC"]) ){

            $this->set_relation( $new_master->get_id(), $new_master->get_nodetype(), [ "policy" => $new_master->get_policies(),"disposition" => "master" ] );

        }elseif ( in_array($this->nodetype, ["PTY","POI"]) ) {
            $this->set_relation( $new_master->get_id(), $new_master->get_nodetype(), [ "disposition" => "master" ] );
        }

    }


    function get_master(){
        /* if it exists, yield this node's referenced 'master' node */

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
        return clone $this->checks;
    }
    function check( string $check ){
        /* using a string key identifier, returns the corresponding value from the node's attributes, provided that the key/value pair exists */

        return array_key_exists($check, $this->checks) ? $this->checks[$check] : null;
    }
    function delete_check( string $key ){
        /* removes the entry provided that the k,v pair exists */
        if( array_key_exists($key, $this->checks)){
            unset($this->checks[$key]);
        }
    }
    function set_check( string $key, bool $value ){
        /* modifies the value for a given key */
        $this->checks[$key] = $value;
    }


    /* Policy : associative array functions 

    */

    function get_policies(){
        /* returns the node's policies */

        # if this node has no defined policies...
        if($this->policies == []){
            # but has a 'master' node:
            if($this->check('has_master')){
                # adopt the 'master's policies as thine own.
                $this->policies = $this->get_master()->get_policies();
            }
        }

        return $this->policies;
    }


    function get_policy( string $policy ){
        /* using a string key identifier, returns the corresponding value from the node's policies, provided that the key/value pair exists */

        return array_key_exists($policy, $this->policies) ? $this->policies[$policy] : null ;
    }


    function delete_policy( string $key ){
        /* removes the entry provided that the k,v pair exists */
        if( array_key_exists($key, $this->policies)){
            unset($this->policies[$key]);
        }
    }


    function set_policy( string $key, bool $value ){
        /* modifies the value for a given key */
        $this->policies[$key] = $value;
    }

    # Policy specific functions

    function policy_diff( array $comparison_policy, bool $summary = false){

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

    function get_disposition_from_score( int $diff_score ){
        /* Depending on the total difference between two entity's policies, they will default treat each other differently.
            These magic-number values will be tweaked in further versions for balance.
        */

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
        return clone $this->abilities;
    }
    function get_ability( string $ability ){
        /* using a string key identifier, returns the corresponding value from the node's attributes, provided that the key/value pair exists */
        return array_key_exists($ability, $this->abilities) ? $this->abilities[$ability] : null;
    }
    function delete_ability( string $key ){
        /* removes the entry provided that the k,v pair exists */
        if( array_key_exists($key, $this->abilities)){
            unset($this->abilities[$key]);
        }
    }
    function set_ability( string $key, array $value ){
        /* modifies the value for a given key */
        $this->abilities[$key] = $value;
    }

}



class NGINPHP{

    public $story_struct;
    public $madlibs;
    public $state;

    function __construct( $story_struct, $madlibs, $save_state = null ){

        $this->story_struct = $story_struct;
        $this->madlibs = $madlibs;



        # initialize minimum values
        $this->state = new SimulaeState( [], [], [], [], [] );
        #$this->state->set_actor();

        if($save_state == null){
            throw new Exception('No save state file designated!!');
        }

        # Instantiate each node as a SimulaeNode and add it to the SimulaeState entity index
        foreach( $save_state as $nodetype => $nodes ){
            foreach( $nodes as $node_id => $json_node){
                $this->add_node_json( $node_id, $nodetype, $json_node );
            }
        
        }

    }


    function add_node_json( $node_id, $nodetype, $json_node ){

        /* Given a json-converted php associative array, instantiates the node and adds it to the corresponding state index
        */

        /*if( in_array($nodetype, ["FAC","POI","PTY","LOC","OBJ"]) ){
            throw new Exception("add_node_json() invalid nodetype :".$nodetype, 1);
        } #*/

        $node = new SimulaeNode(   
                    $node_id,
                    $nodetype,
                    $json_node['references'],
                    $json_node['attributes'],
                    $json_node['relations'],
                    $json_node['checks'],
                    $json_node['policies'],
                    $json_node['abilities'] 
                );

        if( $nodetype == "FAC" ){
            $this->state->FAC[$node_id] = $node; 

        }elseif ($nodetype == "POI") {
            $this->state->POI[$node_id] = $node; 

        }elseif ($nodetype == "PTY") {
            $this->state->PTY[$node_id] = $node;

        }elseif ($nodetype == "OBJ") {
            $this->state->OBJ[$node_id] = $node;

        }elseif ($nodetype == "LOC") {
            $this->state->LOC[$node_id] = $node; 

        }

    }

    function add_node( SimulaeNode $node ){

        /* Given a valid SimulaeNode, instantiates the node and adds it to the corresponding state index
        */

        $node_id = $node->id;
        $nodetype = $node->nodetype;


         if( $nodetype == "FAC" ){
            $this->state->FAC[$node_id] = $node; 

        }elseif ($nodetype == "POI") {
            $this->state->POI[$node_id] = $node; 

        }elseif ($nodetype == "PTY") {
            $this->state->PTY[$node_id] = $node; 

        }elseif ($nodetype == "OBJ") {
            $this->state->OBJ[$node_id] = $node; 

        }elseif ($nodetype == "LOC") {
            $this->state->LOC[$node_id] = $node;

        }else{
            throw new Exception('state add_node_json() Invalid node type : '.$nodetype."\n");
        }

    }


    function delete_node( SimulaeNode $node ){

        /* Given a valid SimulaeNode, removes the node from the corresponding state index */

        $node_id = $node->id;
        $nodetype = $node->nodetype;


         if( $nodetype == "FAC" ){
            unset($this->state->FAC[$node_id]); 

        }elseif ($nodetype == "POI") {
            unset($this->state->POI[$node_id]); 

        }elseif ($nodetype == "PTY") {
            unset($this->state->PTY[$node_id]); 

        }elseif ($nodetype == "OBJ") {
            unset($this->state->OBJ[$node_id]); 

        }elseif ($nodetype == "LOC") {
            unset($this->state->LOC[$node_id]); 

        }else{
            throw new Exception('state add_node_json() Invalid node type : '.$nodetype."\n");
        }

    }


    function generate_element(  string $disposition = null, 
                                string $nodetype = null, 
                                array $relations = null ){
        /* creates a new node with random or specified attributes */

        $new_id;
        $attributes = [];
        $policy = [];

        $new_id = random_choice( $this->madlibs["names"] );

        unset($this->madlibs["names"][array_search($new_id, $this->madlibs["names"])]);

        if( is_null($nodetype) ){
            $nodetype = random_choice( [ "FAC", "POI", "PTY", "OBJ", "LOC" ] );
        }

        # references

        # attributes
        if(in_array($nodetype, ["FAC"])) {
            $attributes = [
                "capability_points" => (rand(1,100)*5)
            ];
        }elseif(in_array($nodetype, ["LOC","OBJ"])) {
            $attributes = [
                "defense" => 1
            ];
        }

        # relations

        # checks

        # policy

        if( in_array($nodetype, ["FAC","POI","PTY"])){
            $policy = [
                "Economy" => [
                    random_choice($this->madlibs["policies"]["Economy"]),
                    (rand(0,1000)/1000)],
                "Liberty" => [
                    random_choice($this->madlibs["policies"]["Liberty"]),
                    (rand(0,1000)/1000)],
                "Culture" => [
                    random_choice($this->madlibs["policies"]["Culture"]),
                    (rand(0,1000)/1000)],
                "Diplomacy" => [
                    random_choice($this->madlibs["policies"]["Diplomacy"]),
                    (rand(0,1000)/1000)],
                "Militancy" => [
                    random_choice($this->madlibs["policies"]["Militancy"]),
                    (rand(0,1000)/1000)],
                "Diversity" => [
                    random_choice($this->madlibs["policies"]["Diversity"]),
                    (rand(0,1000)/1000)],
                "Secularity" => [
                    random_choice($this->madlibs["policies"]["Secularity"]),
                    (rand(0,1000)/1000)],
                "Justice" => [
                    random_choice($this->madlibs["policies"]["Justice"]),
                    (rand(0,1000)/1000)],
                "Naturalism" => [
                    random_choice($this->madlibs["policies"]["Naturalism"]),
                    (rand(0,1000)/1000)],
                "Government" => [
                    random_choice($this->madlibs["policies"]["Government"]),
                    (rand(0,1000)/1000)],
            ];
        }

        # abilities

        if( is_null($disposition) ){
            
        }

        /*  public $id;
            public $nodetype;
            public $references;
            public $attributes;
            public $relations;
            public $checks;
            public $policies;
            public $abilities;
        */

        $new_node = new SimulaeNode(    
            $new_id,
            $nodetype, 
            ["name"=>$new_id],         # references
            $attributes,         # attributes   
            [
                "FAC" => [],
                "POI" => [],
                "PTY" => [],
                "OBJ" => [],
                "LOC" => []
            ],         # relations
            [],         # checks
            $policy,    # policy
            []          # abilities
        );

        return $new_node;

    }


    function generate_actions( int $num_options, 
                                SimulaeNode $actor_node, 
                                array $recent_nodes = null ){

        /* Given an actor node, which determines the perspective from which other nodes are viewed, generates actionable mission options for the actor to select from.
        */

        $options = [];

        while( count($options) < $num_options ){

            # randomly determine new nodetype (from pools with more than 1
            # element)
            $nodetype = random_choice( array_filter(
                ["POI","PTY","OBJ","LOC"],
                function($key) {
                    return count( $this->state->get_nodes($key) )>=1;
                }
            ));

            # randomly pick from available nodes
            $chosen_node = random_choice( 
                array_values($this->state->get_nodes($nodetype) ));

            # ascertain node alliegance/control -> get disposition for relevant action type ('friendly','hostile','neutral')
            $relation = $chosen_node->get_relation($actor_node->get_id(), $actor_node->get_nodetype());

            if( ! is_null($relation) ){
                # chosen has relation with acting node
                $disposition = $relation['disposition'];
            }else{
                # no prior disposition -> reassess relationship

                $disposition = $chosen_node->update_relation($actor)['disposition'];
            }

            # select available action based on node type
            $action = random_choice( $this->story_struct[$nodetype][$disposition] );

            if( ! array_key_exists($chosen_node->get_id(), $options) ){
                $options[$chosen_node->get_id()] = [$action, $chosen_node];
            }

            $action = null;
            $chosen_node = null;

        }

        return array_values($options);

    }


    function generate_event(){
        /* Selects a node as event basis, then generates an event based on 
            the node's type. Events are similar to missions, but are
            essentially the mission outcomes of other entities
        */
        throw new Exception("generate_event() not yet implemented!");
    }


    function select_action( array $options, SimulaeNode $actor_node, bool $random_opt = false ){
        /*  To add more interractivity and user-control this function will 
            give several available options to allow the player to 'control' 
            their actions and interract with other nodes in a manner of their 
            choice.
        */

        echo $actor_node->get_reference("name") . ":\n";

        $i = 1;
        foreach( $options as $n_id => list( $ms, $node ) ){
            list($action, $discretion, $rewards, $penalties) = $ms;

            echo "(".$i.") ". $action . " " . $node->summary() . " {" . $discretion ."}\n";
            $i+=1;

        }

        if($random_opt){
            echo "(".$i.") random\n";
        }

        $index = $this->user_choice_integer("", 0, count($options) );

        if( $index >= 0 and $index <= count($options) ){
            return $options[$index];
        }elseif ( $index == count($options) and $random_opt ) {
            return random_choice($options);
        }

    }


    function user_choice_integer( string $msg, int $limit_low, int $limit_high ){
        /*Present user with available options, and allow them to pick
            an option to proceed.
        */

        echo $msg."\n";

        $choice = null;

        while( is_null($choice) ){

            $choice = readline("(#) >");

            if( in_array($choice, ["q","quit","exit","Quit"]) ){
                $this->save();
                exit;
            }

            $index = intval($choice)-1;

            if( $index >= $limit_low and $index <= $limit_high ){
                return $index;
            }

            $choice = null;

        }

    }


    function user_choice_preset( string $msg, array $options ){
        /* Present user with available options, and allow them to pick
            an option to proceed. User must enter the options literally.
        */
        
        echo $msg."\n";

        foreach ($options as $value) {
            echo " / ".$value;
        }
        echo "\n";

        $choice = null;

        while( is_null($choice) ){

            $choice = readline(" >");

            if( in_array($choice, ["q","quit","exit","Quit"]) ){
                $this->save();
                exit;
            }elseif ( in_array($choice, $options)) {
                return $choice;
            }

            echo "Invalid\n";

            $choice = null;

        }
    }


    function display_nodes_terminal( SimulaeNode $actor ){
        # display in-play nodes to terminal output

        echo "Nodes:\n";
        foreach( $this->state->get_all_nodes() as $nodetype => $nodes ){
            foreach( $nodes as $node_id => $node ){
                #$relation = $node->get_relation($actor->get_id(), $actor->get_nodetype());
                #echo "\t" . $node->summary() . " [" . is_null($relation) ? "" : $relation['disposition'] . "]\n";
                echo "\t" . $node->summary($this->state->FAC['ctscorch']) . "\n";
            }
        }

    }


    function action_handler( string $action, string $discretion, array $rewards, array $penalties, SimulaeNode $node ){

        /* Handle resource allocation */

        /* Overt : event generation */
        /* Covert : event generation */
        /* Passive : event generation */

        /* handle mission outcome */
        $outcome = $this->user_choice_preset("Mission successful?", ["y","n"]);

        $consequences = $outcome == "y" ? $rewards : $penalties;

        foreach( $consequences as $cons ){

            if(      $cons == "+rand"){

                echo "+rand\n";

            }elseif ($cons == "-rand") {
                    
                echo "-rand\n";

            }
            elseif ($cons == "+control") {
                
                $node->set_master( $this->state->FAC['ctscorch'] );

                echo "{".$node->get_nodetype()." Controlled} ".$node->summary( $this->state->FAC['ctscorch'] )."\n";

            }elseif ($cons == "-control") {
                    
                echo "-control\n";

            }
            elseif ($cons == "+intel") {
                     
                echo "+intel\n";

                $new = $this->generate_element();

                echo "[New Intel] ".$new->summary( $this->state->FAC['ctscorch'] )."\n";

                $this->add_node( $new );


            }elseif ($cons == "%intel") {
                    
                if( rand(1,20) > 15 ){

                    $new = $this->generate_element();

                    echo "[New Intel] ".$new->summary( $this->state->FAC['ctscorch'] )."\n";

                    $this->add_node( $new );

                }

            }
            elseif ($cons == "+delete") {
                    
                echo "successfully removed ".$node->summary()."\n";

                $this->delete_node( $node );

            }elseif ($cons == "-delete") {
                    
                echo $node->summary()." has been lost\n";

                $this->delete_node( $node );

            }
            elseif ($cons == "+defense") {
                    
                echo "+defense\n";

                $node->set_attribute( "defense" , $node->get_attribute("defense") + 1 );

            }elseif ($cons == "-defense") {
                    
                echo "-defense\n";

                $node->set_attribute( "defense" , max( 0, $node->get_attribute("defense") - 1) );

            }

        }
        

    }


    function start(){

        /* main loop for processing state and actions */

        while(true){

            system('clear');

            # display nodes
            $this->display_nodes_terminal( $this->state->FAC['ctscorch']);

            /*  generate event ?
                    if event occurs, provide extra action options
            */
            echo "\t< event generation >\n" ;

            
            # generate action options -> user selection
            $action_options = $this->generate_actions( 3, $this->state->FAC['ctscorch'] );

            list( list($action, $discretion, $rewards, $penalties), $node) = $this->select_action( $action_options, $this->state->FAC['ctscorch'] );

            echo "chosen: ".$action . " " . $node->summary($this->state->FAC['ctscorch']) . " {" . $discretion ."}\n";

            # Handle action outcome 
            $this->action_handler($action, $discretion, $rewards, $penalties, $node);


            $cmd = readline("\ncontinue [enter] / [q]uit ?> ");
            if($cmd == "q" or $cmd == "quit")
                break;

        }
    }


    function save(){

        /* Convert state and SimulaeNodes  */

        echo "save() executing...";

        $save_file = fopen("test_save.json","w");

        fwrite($save_file, json_encode($this->state->get_all_nodes(), JSON_PRETTY_PRINT
        ) );

        fclose($save_file);

    }

}


function random_choice(array $items){

    /* randomly selects an element from a given array */

    if( is_null($items) or $items == [] ){
        throw new Exception("random_choice() Nothing in $items");
        return;
    }
    return $items[ array_rand($items) ];
}


function main(){

    /* 
        $action_struct  ->  story_struct.json
        $madlibs        ->  madlibs.json
        $save_file      ->  BPRE-save.json
    */

    $action_struct_fn   =   "story_struct.json";
    $madlibs_fn         =   "madlibs.json";
    $save_file_fn       =   "BPRE-save.json";


    $action_struct = json_decode(file_get_contents($action_struct_fn), TRUE);
    $madlibs = json_decode(file_get_contents($madlibs_fn), TRUE);
    $save_file = json_decode(file_get_contents($save_file_fn), TRUE);

    $GLOBALS['ngin'] = new NGINPHP( $action_struct, $madlibs, $save_file );

    $GLOBALS['ngin']->start();

    $GLOBALS['ngin']->save();

    exit;

}

main()

?>

