<?php

namespace Emulsify;

const ESCAPE_OUTPUT = 1,
      RAW_OUTPUT = 2;

class Emulsify
{
   protected $template,
      $selected;

   public function __construct($file)
   {
      if (!file_exists($file))
      {
         trigger_error('Emulsify: file does not exist', E_USER_ERROR);
         return false;
      }

      libxml_use_internal_errors(true);
      $this->template = new \DOMDocument();
      $this->template->loadHTMLFile($file);
      $this->selected = null;
   }

   public function select($id)
   {
      $this->selected = $this->template->getElementById($id);
      return $this;
   }

   public function bind($class, $data, $option = ESCAPE_OUTPUT)
   {
      if (!$this->selected)
      {
         return false;
      }

      if (!($node = $this->firstElementByClass($class, $this->selected)))
      {
         return false;
      }

      foreach ($data as $element)
      {
         $new_node = $node->cloneNode(true);

         if (is_array($element))
         {
            foreach ($element as $sub_class => $sub_data)
            {
               if (strpos($sub_class, ':') !== false)
               {
                  $sub_class = explode(':', $sub_class);
                  $attribute = $sub_class[1];
                  $sub_class = $sub_class[0];

                  $sub_node = $this->firstElementByClass($sub_class, $new_node);
                  $sub_node->setAttribute($attribute, $sub_data);
               }
               else
               {
                  $sub_node = $this->firstElementByClass($sub_class, $new_node);
                  if ($option & ESCAPE_OUTPUT)
                  {
                     $sub_node->nodeValue = $sub_data;
                  }
                  else
                  {
                     $fragment = $this->template->createDocumentFragment();
                     $fragment->appendXML($sub_data);
                     $sub_node->nodeValue = null;
                     $sub_node->appendChild($fragment);
                  }
               }
            }
         }
         else
         {
            if ($option & ESCAPE_OUTPUT)
            {
               $new_node->nodeValue = $element;
            }
            else
            {
               $fragment = $this->template->createDocumentFragment();
               $fragment->appendXML($element);
               $new_node->nodeValue = null;
               $new_node->appendChild($fragment);
            }
         }

         $this->selected->appendChild($new_node);
      }

      // remove node, which was used purely as a template
      $this->selected->removeChild($node);

      return $this;
   }
   
   public function remove()
   {
      if (!$this->selected)
      {
         return false;
      }

      $child = $this->selected;
      $this->selected = $this->selected->parentNode;
      $this->selected->removeChild($child);

      return $this;
   }

   public function attach($emulsify)
   {
      if (is_string($emulsify))
      {
         $templet = new Emulsify($emulsify);
      }

      if (!is_object($emulsify) || get_class($emulsify) != __CLASS__)
      {
         trigger_error('Templet: argument passed to Templet::attach is invalid', E_USER_ERROR);
      }

      if (!$this->selected)
      {
         return false;
      }

      $node = $this->template->importNode($emulsify->saveRendered(), true);
      $this->selected->nodeValue = null;
      $this->selected->appendChild($node);

      return $this;
   }

   public function render()
   {
      return $this->template->saveHTML();
   }

   public function saveRendered()
   {
      $saved = $this->template->saveHTML();
      $this->template->loadHTML($saved);
      return $this->template->getElementsByTagName('*')->item(0);
   }

   protected function firstElementByClass($class, &$node)
   {
      if (!$node)
      {
         return false;
      }

      $find = $node->getElementsByTagName('*');
      foreach ($find as $tag)
      {
         if (in_array($class, explode(' ', $tag->getAttribute('class'))))
         {
            return $tag;
         }
      }

      return false;
   }
}

