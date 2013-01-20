<?php

namespace Emulsify;

const ESCAPE_OUTPUT = 1,
   RAW_OUTPUT = 2;

class Emulsify
{
   protected $template,
      $selected;

   public function __construct($file = null)
   {
      if (!empty($file) && !file_exists($file))
      {
         trigger_error('Emulsify: file does not exist', E_USER_ERROR);
         return false;
      }

      libxml_use_internal_errors(true);

      $this->template = new \DOMDocument();
      if (!empty($file))
      {
         $this->template->loadHTMLFile($file);
      }
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

      if (!($node = $this->_firstElementByClass($class, $this->selected)))
      {
         return false;
      }

      foreach ($data as $element)
      {
         $new_node = $node->cloneNode(true);
         $new_node = $this->_bind($new_node, $element, $option);
         $this->selected->appendChild($new_node);
      }

      // remove node, which was used purely as a template
      $this->selected->removeChild($node);

      return $this;
   }
   
   public function bindPart($part, $element, $option = ESCAPE_OUTPUT)
   {
      if (!is_array($part))
      {
         return false;
      }

      list($node, $target) = $part;

      if (!$node)
      {
         return false;
      }

      $new_node = $node->cloneNode(true);

      $node = $this->_bind($new_node, $element, $option);

      if (empty($target))
      {
         return $node;
      }
      
      $target->appendChild($node);

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

   public function partial($class, $target = null)
   {
      if (!$this->selected)
      {
         return false;
      }

      if (!($base = $this->_firstElementByClass($class, $this->selected)))
      {
         return false;
      }

      $copy = $base->cloneNode(true);
      $this->selected->removeChild($base);

      if (!empty($target))
      {
         if ($target = $this->template->getElementById($target))
         {
            $target->nodeValue = null;
         }
      }

      return array($copy, $target);
   }

   public function attach($emulsify)
   {
      if (!$this->selected)
      {
         return false;
      }

      if (is_string($emulsify))
      {
         $emulsify = new Emulsify($emulsify);
      }

      if (!is_object($emulsify) || get_class($emulsify) != __CLASS__)
      {
         trigger_error('Emulsify: argument passed to Emulsify::attach is invalid', E_USER_ERROR);
      }

      $node = $this->template->importNode($emulsify->_saveRendered(), true);
      $this->selected->nodeValue = null;
      $this->selected->appendChild($node);

      return $this;
   }

   public function render()
   {
      return $this->template->saveHTML();
   }

   protected function _saveRendered()
   {
      $saved = $this->template->saveHTML();
      $this->template->loadHTML($saved);
      return $this->template->getElementsByTagName('*')->item(0);
   }

   protected function _bind($node, $element, $option = ESCAPE_OUTPUT)
   {
      if (is_array($element))
      {
         foreach ($element as $sub_class => $sub_data)
         {
            if (strpos($sub_class, ':') !== false)
            {
               $sub_class = explode(':', $sub_class);
               $attribute = $sub_class[1];
               $sub_class = $sub_class[0];

               $sub_node = $this->_firstElementByClass($sub_class, $node);
               $sub_node->setAttribute($attribute, $sub_data);
            }
            else
            {
               $sub_node = $this->_firstElementByClass($sub_class, $node);
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
            $node->nodeValue = $element;
         }
         else
         {
            $fragment = $this->template->createDocumentFragment();
            $fragment->appendXML($element);
            $node->nodeValue = null;
            $node->appendChild($fragment);
         }
      }

      return $node;
   }

   protected function _firstElementByClass($class, $node)
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

