<?php

namespace MauriceWijnia\Laraberg\Database\Models;

use MauriceWijnia\Laraberg\Database\Models\Base;

class Page extends Base {
  protected $table = 'lb_pages';

  protected $casts = [
    'content' => 'array'
  ];

  protected const permitted = ['title', 'content'];
  protected $fillable = self::permitted;

  /**
   * Creates a page instance with the provided data
   * @param array $data - The content of the body sent by the Gutenberg editor
   * @return Page
   */
  public static function create($data) {
    $params = self::permittedParams($data);
    $page = new Page;
    $page->title = $params['title'];
    $page->content = $params['content'];
    return $page;
  } 

  /**
   * Transform content to wordpress content object
   */
  function setContentAttribute($content) {
    $this->attributes['content'] = json_encode([ 'raw' => $content ]);
  }

  /**
   * Transform content to empty string if null
   * because Gutenberg cannot handle null values
   */
  function getContentAttribute() {
    $content = json_decode($this->attributes['content'], true);
    if ($content['raw'] == null) {
      $content['raw'] = "";
    }
    return $content;
  }

  /**
   * Renders the HTML of the page object
   */
  function render() {
    return $this->renderBlocks();
  }

  function renderBlocks() {
    $split = preg_replace_callback('/<!-- wp:block {"ref":(\d*)} \/-->/', function($matches) {
      return $matches[0] . "\n" . Block::find($matches[1])->content['raw'];
    }, $this->content['raw']);
    return $split;
  }
}