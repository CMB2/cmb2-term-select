# CMB2 Term Select

Special CMB2 Field that allows users to define an autocomplete text field for terms.

### Example
```php
$cmb->add_field( array(
	'name'     => 'Select Category',
	'id'       => 'category_data',
	'desc'     => 'Type the name of the term and select from the options',
	'type'     => 'term_select',
	'taxonomy' => 'category',
	// 'attributes' => array(
	// 	'data-min-length' => 2, // Override minimum lengthy
	// 	'data-delay'      => 100, // Override delay
	// ),
) );
```
