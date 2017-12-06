<?php



 		
function t()
{
	$data=array('test_helper 1'=>'ashikas test helper 1 ');
	 return response()->json($data)->header('foo', 'bar')->send();

}


?>
