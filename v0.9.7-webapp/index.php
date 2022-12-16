<?php

require 'SimulaeNode.php';
require 'NGINPHP-server.php';

$ngin;

$DEBUG = false;

function random_choice(array $items){

    /* randomly selects an element from a given array */

    if( is_null($items) or $items == [] ){
        throw new Exception("random_choice() Nothing in \$items");
        return;
    }
    return $items[ array_rand($items) ];
}

?>


<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
</head>
<body> 
	<?php

		$madlibs = json_decode(file_get_contents("madlibs.json"), true);
		$story_struct = json_decode(file_get_contents("story_struct.json"), true);
		$save_file = json_decode(file_get_contents("BPRE-save.json"), true);

		echo "files loaded<br>";


		$ngin = new NGINPHP( $story_struct, $madlibs, $save_file );
	    $ngin->state->set_actor();

	    if( is_null($ngin->state->actor ) ){
	        throw new Exception(" main() state->actor is null!");
	    }

	    echo "NGIN setup complete<br>";

	    #$ngin->start();

	    #$ngin->save();

	?>

	<div class="input-group mb-3">
	  <div class="input-group-prepend">
	    <span class="input-group-text" id="inputGroup-sizing-default">BS Textbox input</span>
	  </div>
	  <input type="text" class="form-control" aria-label="Default" aria-describedby="inputGroup-sizing-default">
	</div>

</body>
</html>