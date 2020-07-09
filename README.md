# advanced-php-kmeans

K-means clustering for PHP: unsupervised machine learning algorithm made easy using this library. It is one of the rare libraries that handle multidimensional dataset (no limit), which is quite useful if your dataset contains more than 2 fields.

![Generated2DMatrix_preview](https://i.imgur.com/c8FzzSA.png)

## Getting started

To create a new AdvancedKmeans instance, you need to provide how many clusters you want and how many dimensions your dataset contains:

```php
$k = new AdvancedKmeans(5, 2); // I'll get 5 clusters for a 2D dataset (x, y)
```

Then you can import your data :

```php
$k->add([10, 20]); // Line by line
$k->addArray( Array([10, 20], [12, 8], [15, 32])); // Using an array of lines
```

Once you have provided all the data required, you can launch the treatment using the init method:

```php
$k->init();
```

Sum up :

```php
$k = new AdvancedKmeans(CLUSTER_NUMBER, DATA_DIMENSIONS); // DATA_DIMENSIONS = 2 by default
$k->addArray(MY_DATA); // or ->add() to import line by line
$k->init();
```

## Retrive the generated data

You can get the full matrix, which is an array having as many dimensions as your dataset, by using the get method:

```php
$matrix = $k->get();
```

It is possible as well to get an array containing all your data sorted by cluster. Be careful: as all the data will be copied within this array, you might encounter a PHP error if your dataset is much bigger than your allowed memory size.

```php
$results = $k->getByCluster();
```

Finally, it is possible to print a 2D matrix if your dataset is bidimensional as well:

```php
$k->print2DMatrix(MAX_X_SIZE, MAX_Y_SIZE, MIN_X_SIZE=0, MIN_Y_SIZE=0); // You must define the maximum size of the matrix
```

## Notes

This is the first release of the library. Feel free to contact me if you have any question.

