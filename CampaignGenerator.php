
<?php

$ngin;

class SimulaeState{

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


        foreach( $this->FAC as $fac_id => $fac){
            foreach( $this->FAC as $fac_id2 => $fac2){
                
                if($fac_id != $fac_id2){

                    $fac->update_relation($fac2);
                    $fac2->update_relation($fac);

                }

            }    
        }

    }

    function set_actor(){
        $this->actor = $this->FAC[$this->get_actor( $this->FAC, #$random_opt=true 
            )];
    }

    function get_actor( array $options, bool $random_opt=false ){

        echo "pick entity as actor_node (player control):\n";

        $i = 1;
        foreach( $options as $node_id => $node ){

            echo "(".$i.")" . $node->summary() . "\n";
            $i+=1;

        }
        if($random_opt){
            echo "(".$i.") random\n";
        }

        $choice = null;

        while( is_null($choice) ){

            $choice = readline("choice >");

            if( in_array($choice, ["q","quit","exit","Quit"]) ){
                #$this->save();
                exit;
            }

            $index = intval($choice)-1;

            if( $index >= 0 and $index <= count($options) ){
                return $options[$index];
            }elseif ( $index == count($options) and $random_opt ) {
                return random_choice($options);
            }

            $choice = null;

        }

    }


    function get_nodes(){

        return [    "FAC"=>$this->FAC,
                    "POI"=>$this->POI,
                    "PTY"=>$this->PTY,
                    "OBJ"=>$this->OBJ,
                    "LOC"=>$this->LOC
                ];

    }

}


class SimulaeNode{

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

    function summary( SimulaeNode $perspective=null ){
        if( is_null($perspective) ){ 

            return strval($this->nodetype) . " " . strval($this->get_reference("name"));

        }else{
            $relation = $this->get_relation($perspective->get_id(), $perspective->get_nodetype());

            return "<" . strval($relation['disposition']) . "> " . strval($this->nodetype) . " " . strval($this->get_reference("name"));
        }
    }
    function get_all(){
        $totality = [
            "id"            => $this->id,
            "nodetype"      => $this->nodetype,
            "references"    => $this->references,
            "attributes"    => $this->attributes,
            "relations"     => $this->relations,
            "checks"        => $this->checks,
            "policies"      => $this->policies,
            "abilities"     => $this->abilities             

        ];

        return $totality;

    }


    ### Getter/Setters ###

    function get_id(){
        return $this->id;
    }

    function get_nodetype(){
        return $this->nodetype;
    }

    
    /* References : associative array functions 
        
    */

    function get_references(){
        return $this->references;
    }
    function get_reference( string $reference ){
        return array_key_exists($reference, $this->references) ? $this->references[$reference] : null;
    }
    function delete_reference( string $key ){
        unset($this->references[$key]);
    }
    function set_reference( string $key, string $value ){
        $this->references[$key] = $value;
    }

    /* Attributes : associative array functions 

    */

    function get_attributes(){
        return $this->attributes;
    }
    function get_attribute( string $attribute ){
        return array_key_exists($attribute, $this->attributes) ? $this->attributes[$attribute] : null;
    }
    function delete_attribute( string $key ){
        unset($this->attributes[$key]);
    }
    function set_attribute( string $key, mixed $value ){
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
        return $this->relations;
    }


    function get_relation( string $key, string $key_type ){
        if($this->id == $key){
            return [
                "nodetype" => $this->nodetype,
                "policy" => [],
                "reputation" => [0,0],
                "interractions" => 0,
                "disposition" => "actor"
            ];
        }
        return array_key_exists( $key, $this->relations[$key_type] ) ? $this->relations[$key_type][$key] : $this->update_relation($GLOBALS['ngin']->state->$key_type[$key] );
    }


    function delete_relation( string $key, string $key_type ){
        unset($this->relations[$key_type][$key]);
    }


    function update_relation( SimulaeNode $node ){

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


    function get_master(){

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
        return $this->checks;
    }
    function check( string $check ){
        return array_key_exists($check, $this->checks) ? $this->checks[$check] : null;
    }
    function delete_check( string $key ){
        unset($this->checks[$key]);
    }
    function set_check( string $key, bool $value ){
        $this->checks[$key] = $value;
    }


    /* Policy : associative array functions 

    */

    function get_policies(){

        if($this->policies == []){
            if($this->check('has_master')){
                $this->policies = $this->get_master()->get_policies();
            }else{}
        }

        return $this->policies;
    }
    function get_policy( string $policy ){
        return array_key_exists($policy, $this->policies) ? $this->policies[$policy] : null ;
    }
    function delete_policy( string $key ){
        unset($this->policies[$key]);
    }
    function set_policy( string $key, bool $value ){
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

        $policies = [
            "Economy" => ["Communist", "Socialist", "Indifferent", "Capitalist", "Free-Capitalist"],
            "Liberty" => ["Authoritarian", "Statist", "Indifferent", "Libertarian", "Anarchist"],
            "Culture" => ["Traditionalist", "Conservative", "Indifferent", "Progressive", "Accelerationist"],
            "Diplomacy" => ["Globalist", "Diplomatic", "Indifferent", "Patriotic", "Nationalist"],
            "Militancy" => ["Militarist", "Strategic", "Indifferent", "Diplomatic", "Pacifist"],
            "Diversity" => ["Homogenous", "Preservationist", "Indifferent", "Heterogeneous", "Multiculturalist"],
            "Secularity" => ["Apostate", "Secularist", "Indifferent", "Religious", "Devout"],
            "Justice" => ["Retributionist", "Punitive", "Indifferent", "Correctivist", "Rehabilitative"],
            "Naturalism" => ["Ecologist", "Naturalist", "Indifferent", "Productivist", "Industrialist"],
            "Government" => ["Democratic", "Republican", "Indifferent", "Oligarchic", "Monarchist"]
        ];

        return array_search( $policy, $policies[$factor] );
    }

    function get_disposition_from_score( int $diff_score ){

        if( $diff_score <= 5 ){
            return "friendly";
        }elseif($diff_score <= 10 ){
            return "neutral";
        }elseif($diff_score > 10 ){
            return "hostile";
        }

    }


    /* Abilities : associative array functions 

    */

    function get_abilities(){
        return $this->abilities;
    }
    function get_ability( string $ability ){
        return array_key_exists($ability, $this->abilities) ? $this->abilities[$ability] : null;
    }
    function delete_ability( string $key ){
        unset($this->abilities[$key]);
    }
    function set_ability( string $key, array $value ){
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

        foreach( $save_state as $nodetype => $nodes ){
            foreach( $nodes as $node_id => $json_node){
                $this->add_node_json( $node_id, $nodetype, $json_node );
            }
        
        }

    }


    function add_node_json( $node_id, $nodetype, $json_node ){

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
        }else{
            throw new Exception('state add_node_json() Invalid node type : '.$nodetype."\n");
        }
    }

    function add_node( SimulaeNode $node ){

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

        $new_id = random_choice( $this->madlibs["names"] );
        unset($this->madlibs["names"][array_search($new_id, $this->madlibs["names"])]);

        if( is_null($nodetype) ){
            $nodetype = random_choice( [ "FAC", "POI", "PTY", "OBJ", "LOC" ] );
        }

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
            [],         # references
            [],         # attributes   
            [],         # relations
            [],         # checks
            [
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
            ],
            []          # abilities
        );

        return $new_node;

        #throw new Exception("<function> not yet implemented!");
    }


    function generate_actions( int $num_options, 
                                SimulaeNode $actor_node, 
                                array $recent_nodes = null ){

        $options = [];

        while( count($options) < $num_options ){

            # randomly determine new nodetype (from pools with more than 1
            # element)
            $nodetype = random_choice( array_filter(
                ["POI","PTY","OBJ","LOC"],
                function($key) {
                    return count( $this->state->get_nodes()[$key] )>=1;
                }
            ));

            # randomly pick from available nodes
            $chosen_node = random_choice( 
                array_values($this->state->get_nodes()[$nodetype]) );

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

            if( ! in_array($options, [$action, $chosen_node]) ){
                array_push($options, [$action, $chosen_node] );
            }

        }

        return $options;

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
        foreach( $options as list( list($action, $discretion, $rewards, $penalties), $node) ){

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
        foreach( $this->state->get_nodes() as $nodetype => $nodes ){
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
                
                echo "+control\n";

            }elseif ($cons == "-control") {
                    
                echo "-control\n";

            }
            elseif ($cons == "+intel") {
                     
                echo "+intel\n";

                $new = $this->generate_element();

                echo "[New Intel] ".$new->summary()."\n";

                $this->add_node( $node );

                #$this->state->$new->get_nodetype()[$new->get_id()] = $node;


            }elseif ($cons == "%intel") {
                    
                if( rand(1,20) > 15 ){



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

            }elseif ($cons == "-defense") {
                    
                echo "-defense\n";

            }

        }
        

    }


    function start(){

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

        echo "save() executing...";

        $save_file = fopen("test_save.json","w");

        fwrite($save_file, json_encode($this->state->get_nodes(), JSON_PRETTY_PRINT
        ) );

        fclose($save_file);

    }

}


function random_choice(array $items){
    if( is_null($items) ){
        throw new Exception("random_choice() Nothing in $items");
        return;
    }
    return $items[ array_rand($items) ];
}


function random_choice_asc( array $items ){
    return $items[ array_rand(array_keys($items)) ];
}


function main(){

    $action_struct = json_decode(file_get_contents("story_struct.json"), TRUE);
    $madlibs = json_decode(file_get_contents("madlibs.json"), TRUE);
    $save_file = json_decode(file_get_contents("BPRE-save.json"), TRUE);

    $GLOBALS['ngin'] = new NGINPHP( $action_struct, $madlibs, $save_file );

    $GLOBALS['ngin']->start();

    $GLOBALS['ngin']->save();

    exit;

}

main()

?>
