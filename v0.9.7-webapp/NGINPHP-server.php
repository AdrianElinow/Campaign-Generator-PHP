<?php


class NGINPHP{

    public $story_struct;
    public $madlibs;
    public $state;

    function __construct( $story_struct, $madlibs, $save_state = null ){


        $this->story_struct = $story_struct;
        $this->madlibs = $madlibs;

        # initialize minimum values
        $this->state = new SimulaeState( [], [], [], [], [] );

        if($save_state == null){
            throw new Exception('No save state file designated!!');
        }

        # Instantiate each node as a SimulaeNode and add it to the SimulaeState entity index
        foreach( $save_state as $nodetype => $nodes ){
            foreach( $nodes as $node_id => $json_node){
                $this->add_node_json( $node_id, $nodetype, $json_node );
            }
        }

        if( count(array_filter(
            ["POI","PTY","OBJ","LOC"],
            function($key) {
                return count( $this->state->get_nodes($key) )>=1;
            })) == 0 ){
            echo "No entities in save file.\n";
            exit;
        }

    }


    function add_node_json( $node_id, $nodetype, $json_node ){

        /* Given a json-converted php associative array, instantiates the node and adds it to the corresponding state index
        */

        /*if( in_array($nodetype, ["FAC","POI","PTY","LOC","OBJ"]) ){
            throw new Exception("add_node_json() invalid nodetype :".$nodetype, 1);
        } #*/

        if($GLOBALS["DEBUG"]){ echo "DEBUG{NGINPHP add_node_json($node_id, $nodetype, ...)}\n"; }

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

        if($GLOBALS["DEBUG"]){ echo "DEBUG{NGINPHP add_node(".$node->get_id().")}\n"; }

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

        if($GLOBALS["DEBUG"]){ echo "DEBUG{NGINPHP delete_node(".$node->get_id().")}\n"; }

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

        if($GLOBALS["DEBUG"]){ echo "DEBUG{NGINPHP generate_element(...)}\n"; }

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
                    random_choice(["Communist", "Socialist", "Indifferent", "Capitalist", "Free-Capitalist"]),
                    (rand(0,1000)/1000)],
                "Liberty" => [
                    random_choice(["Authoritarian", "Statist", "Indifferent", "Libertarian", "Anarchist"]),
                    (rand(0,1000)/1000)],
                "Culture" => [
                    random_choice(["Traditionalist", "Conservative", "Indifferent", "Progressive", "Accelerationist"]),
                    (rand(0,1000)/1000)],
                "Diplomacy" => [
                    random_choice(["Globalist", "Diplomatic", "Indifferent", "Patriotic", "Nationalist"]),
                    (rand(0,1000)/1000)],
                "Militancy" => [
                    random_choice(["Militarist", "Strategic", "Indifferent", "Diplomatic", "Pacifist"]),
                    (rand(0,1000)/1000)],
                "Diversity" => [
                    random_choice(["Homogenous", "Preservationist", "Indifferent", "Heterogeneous", "Multiculturalist"]),
                    (rand(0,1000)/1000)],
                "Secularity" => [
                    random_choice(["Apostate", "Secularist", "Indifferent", "Religious", "Devout"]),
                    (rand(0,1000)/1000)],
                "Justice" => [
                    random_choice(["Retributionist", "Punitive", "Indifferent", "Correctivist", "Rehabilitative"]),
                    (rand(0,1000)/1000)],
                "Naturalism" => [
                    random_choice([    "Ecologist", "Naturalist", "Indifferent", "Productivist", "Industrialist"]),
                    (rand(0,1000)/1000)],
                "Government" => [
                    random_choice(["Democratic", "Republican", "Indifferent", "Oligarchic", "Autocratic"]),
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

        if($GLOBALS["DEBUG"]){ echo "DEBUG{NGINPHP generate_actions($num_options, ".$actor_node->get_id().", ...)}\n"; }

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


    function FAC_AI_action(){
        /* Performs an action as a given faction */

        throw new Exception("FAC_AI_action() not yet implemented!");

    }


    function select_action( array $options, SimulaeNode $actor_node, bool $random_opt = false ){
        /*  To add more interractivity and user-control this function will 
            give several available options to allow the player to 'control' 
            their actions and interract with other nodes in a manner of their 
            choice.
        */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{NGINPHP select_action(..., ".$actor_node->get_id().", ...)}\n"; }

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

    function user_choice_array(string $msg, array $options, bool $random_opt = false, bool $simulaenode_options = false){

        /* Present user with available options, and allow them to pick
            an option to proceed.
        */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{NGINPHP user_choice_array( \"$msg\", ..., ..., ...)}\n"; }
        
        echo $msg . ":\n";

        #$i = 1;
        foreach( $options as $i => $item ){

            echo $simulaenode_options ? 
                ("(".($i+1).") ".$item->summary()."\n") : 
                ("(".($i+1).") $item }\n") ;
            #$i+=1;

        }

        if($random_opt){
            echo "(".($i+2).") random\n";
        }

        $index = $this->user_choice_integer("", 0, count($options) );

        if ( $index == count($options) and $random_opt ) {

            $chosen = random_choice($options)->summary();
            
            return $chosen;
        }elseif( $index >= 0 and $index <= count($options) ){

            return $options[$index];

        }

    }


    function user_choice_integer( string $msg, int $limit_low, int $limit_high ){
        /*Present user with available options, and allow them to pick
            an option to proceed.
        */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{NGINPHP user_choice_integer(\"$msg\", $limit_low, $limit_high)}\n"; }

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


    function user_choice_preset( string $msg, array $options, bool $simulaenode_options = false ){
        /* Present user with available options, and allow them to pick
            an option to proceed. User must enter the options literally.
        */

        if($GLOBALS["DEBUG"]){ echo "DEBUG{NGINPHP user_choice_preset($msg, ..., ...)}\n"; }
        
        echo $msg."\n";

        if ( is_null($options) or count($options) == 0 ) {
            
            echo "Warning! user_choice_preset() | \$options may not be empty";
            return;

            #throw new Exception("user_choice_preset() \$options may not be empty");

        }

        foreach ($options as $value) {

            if ($simulaenode_options) {
                echo "\t".$value->summary();
            }else{
                echo "\t".$value;
            }
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

        if($GLOBALS["DEBUG"]){ echo "DEBUG{NGINPHP display_nodes_terminal(".$actor->get_id().")}\n"; }

        echo "Nodes:\n";
        foreach( $this->state->get_all_nodes() as $nodetype => $nodes ){
            foreach( $nodes as $node_id => $node ){

                echo "\t" . $node->summary($actor) . "\n";
            }
        }

    }


    function action_handler( string $action, string $discretion, array $rewards, array $penalties, SimulaeNode $node, SimulaeNode $actor ){

        if($GLOBALS["DEBUG"]){ echo "DEBUG{NGINPHP action_handler($action, $discretion, ..., ..., ".$node->get_id().", ".$actor->get_id().")}\n"; }

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

                array_push($consequences, random_choice(["+intel","+event","-event","+hostile","+neutral","+friendly","+affiliate"]) );

            }elseif ($cons == "-rand") {
                    
                echo "-rand\n";

                array_push($consequences, random_choice(["-event","+hostile","+neutral"]) );

            }
            elseif ($cons == "+control") {
                /*
                if( $node->check("has_master") ){
                    $node->get_relation( $a );
                }
                */

                $node->set_master( $actor );

                echo "{".$node->get_nodetype()." Controlled} ".$node->summary( $this->state->get_actor() )."\n";

            }elseif ($cons == "-control") {
                    
                echo "-control\n";

            }
            elseif ($cons == "+intel") {
                     
                echo "+intel\n";

                $new = $this->generate_element();

                echo "[New Intel] ".$new->summary( $actor )."\n";

                $this->add_node( $new );


            }elseif ($cons == "%intel") {
                    
                if( rand(1,20) > 15 ){

                    $new = $this->generate_element();

                    echo "[New Intel] ".$new->summary( $actor )."\n";

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

            }else{

                echo $cons."\n";

            }

        }
        

    }


    function start(){

        /* main loop for processing state and actions */

        while(true){

            # display nodes
            $this->display_nodes_terminal( $this->state->get_actor() );

            /*  generate event ?
                    if event occurs, provide extra action options
            */
            echo "\t< event generation >\n" ;

            
            # generate action options -> user selection
            $action_options = $this->generate_actions( 5, $this->state->get_actor() );

            list( list($action, $discretion, $rewards, $penalties), $node) = $this->select_action( $action_options, $this->state->get_actor() );

            echo "chosen: ".$action . " " . $node->summary($this->state->get_actor()) . " {" . $discretion ."}\n";

            # Handle action outcome 
            $this->action_handler($action, $discretion, $rewards, $penalties, $node, $this->state->get_actor() );


            $cmd = readline("\ncontinue [enter] / [q]uit ?> ");
            if($cmd == "q" or $cmd == "quit")
                break;

        }
    }


    function save(){

        /* Convert state and SimulaeNodes  */

        echo "save() executing...";

        $GLOBALS["DEBUG"] = false;

        $save_file = fopen("test_save.json","w");

        fwrite($save_file, json_encode($this->state->get_all_nodes(), JSON_PRETTY_PRINT
        ) );

        fclose($save_file);

    }

}


?>