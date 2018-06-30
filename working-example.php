<html>
<head>
<style>
body
{
	font:normal 14px 'Calibri', sans-serif;
	background:#333;
	color:white;
}
</style>
</head>
<body>
	<h1>K-Means clustering using PHP</h1>
	<div style="background:#F6F6F6;color:#333;padding:10px;width:auto">
<?php

include("AdvancedKmeans.br.php");
if(isset($_GET['4D'])) {
	$c = new AdvancedKmeans(5, 4);
	$data = [];
	$i = 0;
	while($i<300) {
		$data[] = Array(mt_rand(1, 140), mt_rand(1,400), mt_rand(1,1000), mt_rand(100,800));
		$i++;
	}
	$c->addArray($data);
	$c->init();
	var_dump($c->get());
}
else
{
	$c = new AdvancedKmeans(8, 2);

	$data = [];
	$i = 0;
	while($i<3000) {
		$data[] = Array(mt_rand(1, 140), mt_rand(1,40) );//, mt_rand(1,1000));
		$i++;
	}


	$c->addArray($data);

	$c->init();

	$c->print2DMatrix(140, 40);
}

?>
</div>
	<br />
	&copy; 2018 Benjamin Rathelot
</body>