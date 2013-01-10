Emulsify
===
PHP template library. Keep HTML and PHP separate, then blend them together.

Emulsify allows you to manipulate and inject data into HTML; without mixing a template language into the HTML.

This keeps the HTML (design) separate from the logic (PHP).

Learn by Example
---
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

