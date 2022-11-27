
<?php

    class SimulaeState{

        public $FAC;
        public $POI;
        public $PTY;
        public $OBJ;
        public $LOC;

        function __construct( $FAC, $POI, $PTY, $OBJ, $LOC ){

            $this->FAC = $FAC;
            $this->POI = $POI;
            $this->PTY = $PTY;
            $this->OBJ = $OBJ;
            $this->LOC = $LOC;

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

        protected $id;
        protected $nodetype;
        protected $references;
        protected $attributes;
        protected $relations;
        protected $checks;
        protected $abilities;

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

        function summary(){
            return strval($this->nodetype) . " " . strval($this->id);
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

        function has_membership(){
            throw new Exception("<function> not yet implemented!");
        }
        function has_ownership(){
            throw new Exception("<function> not yet implemented!");
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
            return array_key_exists( $key, $this->relations[$key_type] ) ? $this->relations[$key_type][$key] : null;
        }
        function delete_relation( string $key, string $key_type ){
            unset($this->relations[$key_type][$key]);
        }
        function set_relation( string $key, string $key_type, mixed $value ){
            $this->relations[$key_type][$key] = $value;
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
            return $this->policies;
        }
        function get_policy( string $policy ){
            return array_key_exists($policy, $this->policies) ? $this->policies[$policy] : null;
        }
        function delete_policy( string $key ){
            unset($this->policies[$key]);
        }
        function set_policy( string $key, bool $value ){
            $this->policies[$key] = $value;
        }

        # Policy specific functions

        function policy_diff( object $comparison_policy, bool $summary = false ){

            $diff_summary = [];
            $diff = 0;

            foreach( $this->policies as $factor => $policy ){

                $delta = abs( $this->get_policy_index( $factor, $policy ) - $this->get_policy_index( $factor, $comparison_policy[$factor]) );

                if($include_summary){
                    $diff_summary[$factor] = [["Agreement", "Civil", "Contentious",  "Opposition", "Diametrically Opposed"][$delta], $delta];
                }
                $diff += $delta;

            }

            if($include_summary){
                return $diff_summary;
            }
            return $diff;

        }

        function get_policy_index( string $factor, string $policy ){

            $policies = [
                "Economy" => ["Communist", "Socialist", "Indifferent", "Capitalist", "Free-Capitalist"],
                "Liberty" => ["Authoritarian", "Statist", "Indifferent", "Libertarian", "Anarchist"],
                "Culture" => ["Traditionalist", "Conservative", "Indifferent", "Progressive", "Accelerationist"],
                "Diplomacy" => ["Globalist", "Diplomatic", "Indifferent", "Patriotic", "Nationalist"],
                "Militancy" => ["Militarist", "Strategic", "Indifferent", "Diplomatic", "Pacifist"],
                "Diversity" => ["Homogenous", "Preservationist", "Indifferent", "Heterogeneous", "Multiculturalist"],
                "Secularity" => ["Apostate", "Secularist", "Indifferent", "Religious", "Devout"],
                "Justice" => ["Retributionist", "Punitive", "Indifferent", "Correctivist", "Rehabilitative"],
                "Natural-Balance" => ["Ecologist", "Naturalist", "Indifferent", "Productivist", "Industrialist"],
                "Government" => ["Democratic", "Republican", "Indifferent", "Oligarchic", "Monarchist"]
            ];

            return array_search( $policy, $policies[$factor] );
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

            if($save_state == null){
                throw new Exception('No save state file designated!!');
            }

            foreach( $save_state as $nodetype => $nodes ){
                foreach( $nodes as $node_id => $json_node){
                    $this->add_node( $node_id, $nodetype, $json_node );
                }
            }


        }

        function add_node( $node_id, $nodetype, $json_node ){

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
                throw new Exception('state add_node() Invalid node type : '.$nodetype."\n");
            }
        }


        function generate_element(){
            /* creates a new node with random attributes */
            throw new Exception("<function> not yet implemented!");
        }


        function generate_actions( int $num_options, 
                                    array $recent_nodes = null, 
                                    SimulaeNode $actor_node = null ){

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

                # select available action based on node type
                $action = random_choice( $this->story_struct[$nodetype] );

                array_push($options, [$action[0], $chosen_node] );            

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

        function select_action( array $options, bool $random_opt = false ){
            /*  To add more interractivity and user-control this function will 
            give several available options to allow the player to 'control' 
            their actions and interract with other nodes in a manner of their 
            choice.
            */
            #throw new Exception("select_action() not yet implemented!");

            $i = 1;
            foreach( $options as list($action, $node) ){

                echo "(".$i.")". $action . " " . $node->summary() . "\n";
                $i+=1;

            }
            if($random_opt){
                echo "(".$i.") random\n";
            }

            $choice = null;

            while( is_null($choice) ){

                $choice = readline("choice >");

                if( in_array($choice, ["q","quit","exit","Quit"]) ){
                    $this->save();
                    exit;
                }

                $index = intval($choice)-1;

                if( $index > 0 and $index <= count($options) ){
                    return $options[$index];
                }elseif ( $index == count($options) and $random_opt ) {
                    return random_choice($options);
                }

                $choice = null;

            }
        }

        function user_choice_array(){
            /*Present user with available options, and allow them to pick
                an option to proceed.
            */
            throw new Exception("user_choice_array() not yet implemented!");
        }

        function user_choice_preset(){
            /* Present user with available options, and allow them to pick
                an option to proceed.
            */
            throw new Exception("user_choice_preset() not yet implemented!");
        }

        function display_nodes_terminal(){
            # display in-play nodes to terminal output

            echo "Nodes:\n";
            foreach( $this->state->get_nodes() as $nodetype => $nodes ){
                foreach( $nodes as $node_id => $node ){
                    echo "\t" . $node->summary() . "\n";
                }
            }

        }


        function start(){

            echo "start() executing...";

            while(true){

                system('clear');

                # display nodes
                $this->display_nodes_terminal();

                /*  generate event ?
                        if event occurs, provide extra action options
                */
                echo "\t< event generation >\n" ;

                
                # generate action options -> user selection
                $action_options = $this->generate_actions( 3 );

                $selected_action = $this->select_action( $action_options );

                echo "chosen: ".$selected_action[0]." ".$selected_action[1]->summary()."\n";

                # Handle action outcome 


                $cmd = readline("\ncontinue [enter] / [q]uit ?> ");
                if($cmd == "q" or $cmd == "quit")
                    break;

            }
        }


        function save(){

            echo "save() executing...";

            echo "save() function is not conservative! Data will be lost in the save file";

            $save_file = fopen("test_save.json","w");

            fwrite($save_file, json_encode($this->state->get_nodes(), JSON_PRETTY_PRINT) );

            fclose($save_file);

        }

    }

    function random_choice(array $items){
        $choice = $items[ array_rand($items) ];

        return $choice;
        #return $items[ array_rand($items) ];
    }

    function random_choice_associative( array $items ){
        return $items[ array_rand(array_keys($items)) ];
    }
    

    function main(){

        $action_struct = json_decode(file_get_contents("story_struct.json"), TRUE);
        $madlibs = json_decode(file_get_contents("madlibs.json"), TRUE);
        $save_file = json_decode(file_get_contents("save.json"), TRUE);

        $ngin = new NGINPHP( $action_struct, $madlibs, $save_file );

        readline("start?\n");
        $ngin->start();

        $ngin->save();

        exit;

    }

    main()

?>
