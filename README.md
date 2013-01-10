# Emulsify
PHP template library. Keep HTML and PHP separate, then blend them together.

Emulsify allows you to manipulate and inject data into HTML; without mixing a template language into the HTML.

This keeps the HTML (design) separate from the logic (PHP).

### Overview
Create a new Emulsify instance by calling `Emulsify\Emulsify()` with an HTML file.
```
$template = new Emulsify\Emulsify('template.htm');
```

Next, select an HTML element by ID; we do this to get a "block" of HTML to manipulate.
```
$template->select('user-list');
```

Now we can bind data to a node within this block.

We can pass data in two ways (though they are similar).

1. If you just want to push some raw text, we would set up an array as follows:
```
$data =  array(
   'JasonBourne',
   'dwebb',
   // ... etc.
);
```

2. Or, if we want to populate further elements within the chosen `bind`ed class, we can pass a more complex array.
```
$data = array(
   array(
      'username' => 'Jason',
      'username:href' => 'http://example.com/Jason',
      'twitter:href' => 'https://twitter.example.org/jasonbourne',
   ),
   array(
      'username' => 'Dave',
      'username:href' => 'mailto:david@webb.dom',
      'twitter:href' => 'https://twitter.example.org/dwebb',
   ),
);
```

We can then call `bind()` with the class and data array.
```
$template->select('user-list')->bind('user', $data);
```

Finally, we `render()` our template.
```
echo $template->render();
```

### Learn by Example
`template.htm`
```
<!DOCTYPE html>
<html>
   <body>
      <div id="user-list">
         <div class="user">
            <a href="#" class="username">username</a> (<a href="#" class="twitter">twitter</a>)
         </div>
      </div>
   </body>
</html>
```

`userlist-view.php`
```
<?php

require 'emulsify.php';

$data = array(
   array(
      'username' => 'Jason',
      'username:href' => 'http://example.com/Jason',
      'twitter:href' => 'https://twitter.example.org/jasonbourne',
   ),
   array(
      'username' => 'Dave',
      'username:href' => 'mailto:david@webb.dom',
      'twitter:href' => 'https://twitter.example.org/dwebb',
   ),
);

$template = new Emulsify\Emulsify('template.htm');
$template->select('user-list')->bind('user', $data);
echo $template->render();
```

The expected output should be:
```
<!DOCTYPE html>
<html>
   <body>
      <div id="user-list">
         <div class="user">
            <a href="http://example.com/Jason" class="username">Jason</a> (<a href="https://twitter.example.org/jasonbourne" class="twitter">twitter</a>)
         </div>
         <div class="user">
            <a href="mailto:david@webb.dom" class="username">Dave</a> (<a href="https://twitter.example.org/dwebb" class="twitter">twitter</a>)
         </div>
      </div>
   </body>
</html>
```

