Robido Rewrites
=========

This is a simple WordPress plugin that is designed for developers who want to quickly and painlessly add custom rewrite rules that map to a custom template they create.

The settings are controlled entirely by the theme, and require for you to create your own templates. Read below for instructions on how to set this up.

Theme Rewrite Settings
-----------

To get started, you'll need to add a filter to your theme. There are two ways to do this currently, one is a very simple rewrite just to get the template to load, and the other is more advanced. 

#### A Simple Rewrite:
```php
// Robido simple rewrite example
function my_custom_rewrite_rules( $rules ) {
	$rules['property'] = get_stylesheet_directory() . '/template-property.php';
	return $rules;
}
add_filter( 'robido_rewrites', 'my_custom_rewrite_rules' );
```

**Note:** _This will take any URL like http://yourwebsite.com/**property** and rewrite it to render the file **template-property.php** in your theme's directory. You can pass $_GET params on the end of the URL like you normally would with /property/?id=12&foo=bar and the directives past **/property/** will be ignored._

#### An Advanced Rewrite:
```php
// Robido simple rewrite example
function my_custom_rewrite_rules( $rules ) {
	$rules['property'] = array(
		'template'	=> get_stylesheet_directory() . '/template-property.php',
		'params'	=> array( 'id', 'foo' ),
	);
	return $rules;
}
add_filter( 'robido_rewrites', 'my_custom_rewrite_rules' );
```

**Note:** _This is the same rewrite as the Simple Rewrite example above, except on a URL like **/property/12/bar/** it will map $_GET params in the order of the params array. This means **$_GET['id'] = 12** and **$_GET['foo'] = 'bar'**

Here's a sample template file that I used to play around and test this plugin (it's the template-property.php file from the filter above):

```php
<?php
	get_header();
	global $wp_query, $robido_rewrites;
?>

<h1>Our ID is: <?php echo get_query_var( 'id' ); ?></h1>
<h5>It can also be accessed like this: <?php echo $_GET['id']; ?></h5>

<?php
	echo '<h1>$wp_query->query_vars</h1><pre>';
	print_r( $wp_query->query_vars );
	echo '</pre>';

	echo '<h1>$robido_rewrites</h1><pre>';
	print_r( $robido_rewrites );
	echo '</pre>';

	get_footer();
```

The URL that I tested this on was http://theurlformysite.com/property/?id=12

To-do
----
* Ability to control native $wp_query object properties in the rewrite settings filter (this way you can tell WordPress your rewrite is an archive and use is_archive() and such)
* This is super barebones so please feel free to find me on IRC (freenode) in #wordpress, ##wordpress, and #robido and let me know what you'd like added

License
----

We use the GPLv2 license which allows anyone to play around with our code in any way they like, have fun!