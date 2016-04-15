Robido Rewrites
=========

This is a simple WordPress plugin that is designed for developers who want to quickly and painlessly add custom rewrite rules that map to a custom template they create.

The settings are controlled entirely by the theme, and require for you to create your own templates. Read below for instructions on how to set this up.

Theme Rewrite Settings
-----------

To get started, you'll need to add a filter to your theme. Here's an example of how to add a /property rewrite that will map to a custom template file called template-property.php that lives in your theme's folder:

```php
// Robido rewrites settings
function my_custom_rewrite_rules() {
	$rewrites = array(
		'rewrites'  => array(
			'property'  => get_stylesheet_directory() . '/template-property.php',
		),
	);
	return $rewrites;
}
add_action( 'robido_rewrites', 'my_custom_rewrite_rules' );
```

To-do
----
What needs to be done
* This is super barebones so please feel free to find me on IRC (freenode) in #wordpress, ##wordpress, and #robido and let me know what you'd like added

License
----

We use the GPLv2 license which allows anyone to play around with our code in any way they like, have fun!