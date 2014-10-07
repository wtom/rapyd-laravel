<?php

namespace Zofe\Rapyd;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Paginator;
use Zofe\Rapyd\Exceptions\DataSetException;

class DataSet extends Widget
{

    public $cid;
    public $source;

    /**
     *
     * @var \Illuminate\Database\Query\Builder
     */
    public $query;
    public $data = array();
    public $hash = '';
    public $url;
    public $key = 'id';

    /**
     * @var \Illuminate\Pagination\Paginator
     */
    public $paginator;

    protected $orderby_field;
    protected $orderby_direction;
    protected $type;
    protected $limit;
    protected $orderby;
    protected $orderby_uri_asc;
    protected $orderby_uri_desc;

    /**
     * @param $source
     *
     * @return static
     */
    public static function source($source)
    {
        $ins = new static();
        $ins->source = $source;

        \Event::listen('dataset.sort', array($ins, 'sort'));
        \Event::listen('dataset.page', array($ins, 'page'));
        
        //inherit cid from datafilter
        if ($ins->source instanceof \Zofe\Rapyd\DataFilter\DataFilter) {
            $ins->cid = $ins->source->cid;
        }
        //generate new component id
        else {
            $ins->cid = $ins->getIdentifier();
        }

        return $ins;
    }

    protected function table($table)
    {
        $this->query = DB::table($table);

        return $this->query;
    }

    /**
     * @param string $field
     * @param string $dir
     *
     * @return mixed
     */
    public function orderbyLink($field, $dir = "asc")
    {
        $dir = ($dir == "asc") ? '' : '-';
        return Rapyd::linkRoute('orderby', array($dir, $field));
    }

    public function orderBy($field, $direction="asc")
    {
        $this->orderby = array($field, $direction);
        return $this;
    }

    public function onOrderby($field, $dir="asc")
    {
        $dir = ($dir == "asc") ? '' : '-';
        return Rapyd::isRoute('orderby', array($dir, $field));
    }

    /**
     * @param $items
     *
     * @return $this
     */
    public function paginate($items)
    {
        $this->limit = $items;

        return $this;
    }

    public function build()
    {
        \Event::flush('dataset.sort');
        \Event::flush('dataset.page');
        
        
        if (is_string($this->source) && strpos(" ", $this->source) === false) {
            //tablename
            $this->type = "query";
            $this->query = $this->table($this->source);
        } elseif (is_a($this->source, "\Illuminate\Database\Eloquent\Model")) {
            $this->type = "model";
            $this->query = $this->source;
            $this->key = $this->source->getKeyName();

        } elseif ( is_a($this->source, "\Illuminate\Database\Eloquent\Builder")) {
            $this->type = "model";
            $this->query = $this->source;
            $this->key = $this->source->getModel()->getKeyName();

        } elseif ( is_a($this->source, "\Illuminate\Database\Query\Builder")) {
            $this->type = "model";
            $this->query = $this->source;

        } elseif ( is_a($this->source, "\Zofe\Rapyd\DataFilter\DataFilter")) {
           $this->type = "model";
           $this->query = $this->source->query;

            if (is_a($this->query, "\Illuminate\Database\Eloquent\Model")) {
                $this->key = $this->query->getKeyName();
            } elseif ( is_a($this->query, "\Illuminate\Database\Eloquent\Builder")) {
                $this->key = $this->query->getModel()->getKeyName();
            }

        }
        //array
        elseif (is_array($this->source)) {
            $this->type = "array";
        } else {
            throw new DataSetException(' "source" must be a table name, an eloquent model or an eloquent builder. you passed: ' . get_class($this->source));
        }


        //build subset of data
        switch ($this->type) {
            case "array":
                //orderby
                if (isset($this->orderby)) {
                    list($field, $direction) = $this->orderby;
                    $column = array();
                    foreach ($this->source as $key => $row) {
                        $column[$key] = is_object($row) ? $row->{$field} : $row[$field];
                    }
                    if ($direction == "asc") {
                        array_multisort($column, SORT_ASC, $this->source);
                    } else {
                        array_multisort($column, SORT_DESC, $this->source);
                    }
                }

                // @TODO: must be refactored
                $this->paginator = Paginator::make($this->source, count($this->source), $this->limit ? $this->limit : 1000000);
                //find better way
                $this->data = array_slice($this->source, $this->paginator->getFrom() - 1, $this->limit);
                break;

            case "query":
            case "model":
                //orderby

                if (isset($this->orderby)) {
                    $this->query = $this->query->orderBy($this->orderby[0], $this->orderby[1]);
                }
                //limit-offset
                if (isset($this->limit)) {
                    $this->paginator = $this->query->paginate($this->limit);
                    $this->data = $this->paginator;
                } else {
                    $this->data = $this->query->get();
                }

                break;
        }

        return $this;
    }

    public function sort($direction, $field)
    {
        $this->orderby_field = $field;
        $this->orderby_direction = ($direction === "-") ? "desc" : "asc";
        $this->orderBy($this->orderby_field, $this->orderby_direction);
    }

    public function page($page)
    {
        \Paginator::setCurrentPage($page);
    }
    
    /**
     * @return $this
     */
    public function getSet()
    {
        $this->build();

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $view
     *
     * @return mixed
     */
    public function links($view = null)
    {
        if ($this->limit) {
            $links = $this->paginator->links($view);
            $newlinks = preg_replace('@href="(.*\?page=(\d+))"@U',  'href="'.Rapyd::linkRoute('page', '$2').'"', $links);
            return $newlinks;
        }
    }

    public function havePagination()
    {
        return (bool) $this->limit;
    }
}
