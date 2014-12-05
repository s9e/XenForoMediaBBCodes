How to create a PHP callback to change the way a media site is displayed
========================================================================
These instructions are meant for people who have some experience with PHP.

First you need to choose a name for the PHP class. In this example, we use `s9e_Custom` for the class name. Create a file named `Custom.php`.

Callbacks receive two arguments: the original HTML and an array of variables that depend on each media site. Usually it contains an element named `id` containing the ID or the video. The callback must return the new HTML. In this example we create a method named `youtube` which will wrap the original HTML in a div. Note that you should **always** escape the variables when you output them.

```php
<?php

class s9e_Custom
{
	public static function youtube($html, array $vars)
	{
		return '<div class="youtube" data-id="' . htmlspecialchars($vars['id']) . '">' . $html . '</div>';
	}
}
```

Upload this file to your webserver in the `library/s9e/` directory. The path to the file will be `library/s9e/Custom.php`. If we had chosen `Foo_Bar` as the class name, the file would be `library/Foo/Bar.php`.

Now you need to configure XenForo to use your custom callback. Go to the admin panel in Options > s9e Media Pack and enter the name of the site you want to modify and the name of the callback in the form `site=class::method`. You can modify as many sites as you want by entering each custom callback on a separate line.

Click "Save Changes" and you're done.

