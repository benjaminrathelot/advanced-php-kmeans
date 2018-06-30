<?php
/*
	K-Means clustering Class - Copyright 2018 Benjamin Rathelot
	https://github.com/benjaminrathelot/php-kmeans
	https://fr.linkedin.com/in/benjaminrathelot

													Check this out => https://notify.pm/
	Simple usage:
	$data = Array([10, 20], [12, 8], [32, 37]);
	$k = new AdvancedKmeans($clusterNumbers, $dataDimensions {2 in the current example});
	$k->addArray($data);
	$k->init();
	...
	$matrix = $k->get();
	$sortedBycluster = $k->getByCluster();
	$k->print2DMatrix(40, 40);

*/

class AdvancedKmeans {
	private $matrix;
	private $centroids;
	private $cI;
	private $hasChange;
	private $dimension;
	private $clusterN;
	private $minValues;
	private $maxValues;
	private $currentBestDistance;
	private $centroidSums;
	private $centroidCounts;
	private $returnByCluster;

	public function __construct($clusters = 3, $dimension=2) {
		$this->matrix = Array();
		$this->centroids = Array();
		$this->cI = 0;
		$this->hasChange = true;
		$this->dimension = $dimension;
		$this->clusterN = $clusters;
		$this->minValues = Array();
		$this->maxValues = Array();
		$this->currentBestDistance = null;
		$this->centroidSums = Array();
		$this->centroidCounts = Array();
		$this->returnByCluster = Array();
	}

	public function init() {
		$i = 0;
		while($i<$this->clusterN) {
			$this->addRandomCentroid();
			$i++;
		}
		$this->affectPoints();
	}

	public function add($data, $isCentroid=false) {
		if(is_array($data) && count($data) == $this->dimension) {
			foreach($data as $depth=>$val) {
				if(!isset($this->minValues[$depth]) || $this->minValues[$depth]>$val) {
					$this->minValues[$depth] = $val;
				}
				if(!isset($this->maxValues[$depth]) || $this->maxValues[$depth]<$val) {
					$this->maxValues[$depth] = $val;
				}
			}
			if($isCentroid===false){
				if(!isset($this->matrix[$data[0]])) {
					$this->matrix[$data[0]] = Array();
				}
				$pointer = &$this->matrix[$data[0]];
			}
			else
			{
				if(!isset($this->centroids[$data[0]])) {
					$this->centroids[$data[0]] = Array();
				}
				$pointer = &$this->centroids[$data[0]];
			}
			unset($data[0]);
			foreach($data as $k=>$d) {
				if(!isset($pointer[$d])) {
					if(count($data) == 1) {
						if($isCentroid===false){
							$pointer[$d] = false;
						}
						else
						{ 

							$pointer[$d]  = $isCentroid;
						}
					}
					else
					{
						$pointer[$d] = Array();
						$pointer = &$pointer[$d];

					}
				}
				else
				{
					if(count($data) == 1) {
						if($isCentroid===false){
							$pointer[$d] = false;
						}
						else
						{
							$pointer[$d]  = $isCentroid;
						}
					}
					else
					{
						$pointer = &$pointer[$d];
					}
				}
				unset($data[$k]);
			}
			return true;
		}
		return false;
	}

	public function addArray($data) {
		$rt = true;
		if(is_array($data)) {
			foreach($data as $single){
				$x = $this->add($single);
				if(!$x)$rt = false;
			}
			return $rt;
		}
		
		return false;
	}

	private function addRandomCentroid() {
		$depth = 0;
		$pointer = &$this->centroids;
		while($depth<$this->dimension) {
			$r = mt_rand($this->minValues[$depth], $this->maxValues[$depth]);
			if(!isset($pointer[$r])) {
				if($depth+1==$this->dimension) {
					$pointer[$r] = $this->cI;
					$this->cI++;
				}
				else
				{
					$pointer[$r] = Array();
					$pointer = &$pointer[$r];
				}
			}
			else
			{
				if($depth+1==$this->dimension) {
					$pointer[$r] = $this->cI;
					$this->cI++;
				}
				else
				{
					$pointer = &$pointer[$r];
				}
			}
			$depth++;
		}
	}

	public function pointDistanceUsingDataArray($xA, $A, $xB, $B) {
		$r = pow((floatval($xB) - floatval($xA)), 2);
		$depth = 0;
		$pA = &$A;
		$pB = &$B;
		while($depth<$this->dimension-1) { 
			reset($pA);
			reset($pB);
			$r += pow((floatval(key($pB)) - floatval(key($pA))), 2);
			$pA = &$pA[key($pA)];
			$pB = &$pB[key($pB)];
			$depth++;
		}
		return sqrt($r);
	}

	public function pointDistance($A, $B) {
		$depth=0;
		$r = 0;
		while($depth<$this->dimension) {
			reset($A);
			reset($B);
			$r += pow((floatval($B[key($B)]) - floatval($A[key($A)])), 2);
			unset($A[key($A)]);
			unset($B[key($B)]);
			$depth++;
		}
		return sqrt($r);
	}

	public function affectPoints() { 
		while($this->hasChange) {
			$this->hasChange = false;
			if(count($this->centroidSums)) { 
				$this->centroids = Array();
				foreach($this->centroidSums as $cId=>$pos) {
					foreach($pos as $k=>$v) {
						$pos[$k] = round($v/$this->centroidCounts[$cId]);
					}
					$this->add($pos, $cId);
				}
				$this->centroidSums = Array();
				$this->centroidCounts = Array();
			}
			$this->affectPoint($this->matrix);
		}
	}

	private function affectPoint(&$point, $path = []) {
		$this->currentBestDistance = null;
		if(is_array($point)) {
			foreach($point as $k=>$v) { 
				$this->affectPoint($point[$k], array_merge($path, [$k]));
			}
		}
		else
		{ 
			// Now we can affect
			$current = $point;
			foreach($this->centroids as $k=>$centroid) {
				$this->affectBestCentroid($point, $path, $centroid, [$k]);
			}

			if($point!=$current) { 
				$this->hasChange = true;
			}

			if(!isset($this->centroidSums[$point])) {
				$this->centroidSums[$point] = Array();
			}

			if(!isset($this->centroidCounts[$point])) {
				$this->centroidCounts[$point] = 1;
			}
			else
			{
				$this->centroidCounts[$point]++;
			}
			foreach($path as $k=>$pos) {
				if(!isset($this->centroidSums[$point][$k])) {
					$this->centroidSums[$point][$k] = 0;
				}
				$this->centroidSums[$point][$k] +=$pos;
			}
		}
	}

	private function affectBestCentroid(&$point, $pointPath, &$centroid, $centroidPath) {
		if(is_array($centroid)) {
			foreach($centroid as $k=>$c) {
				$this->affectBestCentroid($point, $pointPath, $centroid[$k], array_merge($centroidPath, [$k]));
			}
		}
		else
		{
			$dist = $this->pointDistance($pointPath, $centroidPath);
			
			if($this->currentBestDistance==null||$this->currentBestDistance>$dist) {
				$point = $centroid;
				$this->currentBestDistance = $dist;
			}
		}
	}

	private function proceedReturnByCluster(&$point, $pointPath = []) {
		if(is_array($point)) {
			foreach($point as $k=>$v) {
				$this->proceedReturnByCluster($point[$k], array_merge($pointPath, [$k]));
			}
		}
		else
		{
			if(!isset($this->returnByCluster[$point])) {
				$this->returnByCluster[$point] = Array();
			}
			$this->returnByCluster[$point][] = $pointPath;
		}
	}

	public function get(){
		return $this->matrix;
	}

	// Returns an array containing ClusterN => [Cluster data...] => Careful: the full dataset will be copied
	// => So it might be heavy with large dataset
	public function getByCluster() {
		$this->proceedReturnByCluster($this->matrix);
		return $this->returnByCluster;
	}


	public function print2DMatrix($maxX, $maxY, $minX=0, $minY=0, $colors=["66996C", "E14054", "94B7C8", "C69C72", "2d4059", "2f89fc", "860f44", "a6ed8e", "ffaf87", "658525", "74dac6"]) {
	if($this->dimension==2) {
		$iX = $minX;
		$iY = $maxY;
		echo "<i>(y)</i><br />";
		while($iY>=$mixY) {
			echo (($iY>9)?$iY:"0".$iY)." |";
			while($iX<=$maxX) {
				//if(!isset($colors[$this->matrix[$iX][$iY]])) $colors[$this->matrix[$iX][$iY]] = $colors[mt_rand(0, count($colors)-1)];
				echo isset($this->matrix[$iX][$iY])?'<span style="color:'.$colors[$this->matrix[$iX][$iY]].'">'.$this->matrix[$iX][$iY]."</span>":'<span style="color:silver">_</span>';
				$iX++;
			}
			if($iY==$mixY) echo $maxX." <i>(x)</i>";
			echo "<br />";
			$iX=$mixX;
			$iY--;
		}
	}
	else
	{
		echo "The dataset must be in 2 dimensions.";
	}
}

	// End class
}