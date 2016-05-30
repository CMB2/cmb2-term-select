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
	// 'apply_term' => false, // If set to false, saves the term to meta instead of setting term on the object.
	// 'attributes' => array(
	// 	'data-min-length' => 2, // Override minimum length
	// 	'data-delay'      => 100, // Override delay
	// ),
) );
```
