<?php

class Paginator
{
    private $db = null;
    private $limit;
    private $page;
    private $query;
    private $total;

    /**
     * Constructor
     */
    public function __construct($query, $data_array = array())
    {
        $this->db = Database::get();
        $this->query = $query;
        $query = "SELECT 1 FROM ($query) T";
        $this->db->execute($query, $data_array);
        $this->total = $this->db->getLastNumRows();
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->db = null;
        $this->query = "";
        $this->total = 0;
    }

    public function getData($limit, $page, $data_array = array())
    {
        $this->limit = $limit;
        $this->page = (filter_var($page, FILTER_VALIDATE_INT) && $page > 0) ? $page : 1;

        if ($this->limit == 'all') {
            $query = $this->query;
        } else {
            $query = $this->query . " LIMIT " . (($this->page - 1) * $this->limit) . ", $this->limit";
        }

        $data = $this->db->execute($query, $data_array);

        $result = new stdClass();
        $result->page = $this->page;
        $result->limit = $this->limit;
        $result->total = $this->total;
        $result->data = $data;

        return $result;
    }


    public function createLinks($links, $list_class, $attrs = array(), $method = "get")
    {
        if ($this->limit == 'all') {
            return '';
        }

        $last = ceil($this->total / $this->limit);

        $start =  ($this->page - $links) > 0 ? $this->page - $links : 1;
        $end =  ($this->page + $links) < $last ? $this->page + $links : $last;

        //echo "start=$start, end=$end, last=$last";

        switch($method) {
            case "get":

                /*** desktop pagination ***/
                $html = '<div class="' . $list_class . '">';

                $attrs['page'] = $this->page - 1;
                $class = ($this->page <= 1) ? "disabled" : "";
                $query_string = http_build_query($attrs);
                $href = ($this->page > 1) ? 'href="?' . $query_string . '"' : "";
                $html .= '<a class="item ' . $class . '" ' . $href.'> ← </a>';

                if ($start > 1) {
                    $attrs['page'] = 1;
                    $query_string = http_build_query($attrs);
                    $html .= '<a class="item" href="?' . $query_string . '">1</a>';
                    $html .= '<a class="disabled item">...</a>';
                }

                for ($i = $start ; $i <= $end; $i++) {
                    $attrs['page'] = $i;
                    $class  = ($this->page == $i) ? "active" : "";
                    $query_string = http_build_query($attrs);
                    $html .= '<a class="item '.$class.'" href="?' . $query_string . '">' . $i . '</a>';
                }

                if ($end < $last) {
                    $attrs['page'] = $last;
                    $query_string = http_build_query($attrs);
                    $html .= '<a class="disabled item">...</a>';
                    $html .= '<a class="item"  href="?' . $query_string . '">' . $last . '</a>';
                }

                $attrs['page'] = $this->page + 1;
                $class = ($this->page >= $last) ? "disabled" : "";
                $query_string = http_build_query($attrs);
                $href = ($this->page < $last) ? 'href="?' . $query_string . '"' : "";
                $html .= '<a class="item ' . $class . '" ' . $href.'> → </a>';

                $html .= '</div>';

                /*** mobile pagination ***/
                $html .= "<div class='".$list_class." mobile'>";

                $attrs['page'] = $this->page - 1;
                $class = ($this->page <= 1) ? "disabled" : "";
                $query_string = http_build_query($attrs);
                $href = ($this->page > 1) ? 'href="?' . $query_string . '"' : "";
                $html .= '<a class="item ' . $class . '" ' . $href.'> ← </a>';

                $attrs['page'] = $this->page;
                $query_string = http_build_query($attrs);
                $html .= "<a class='active item' href='?" . $query_string . "'>(".$this->page."/".$last.")</a>";

                $attrs['page'] = $this->page + 1;
                $class = ($this->page >= $last) ? "disabled" : "";
                $query_string = http_build_query($attrs);
                $href = ($this->page < $last) ? 'href="?' . $query_string . '"' : "";
                $html .= '<a class="item ' . $class . '" ' . $href.'> → </a>';

                $html .= "</div>";
                break;
            case "ajax":

                $attr = "";
                foreach($attrs as $key => $val) {
                    $attr = $attr.$key."='".$val."' ";
                }

                /*** desktop pagination ***/
                $html = '<div class="' . $list_class . '">';

                $class = ($this->page <= 1) ? "disabled" : "";
                $href = ($this->page > 1) ? ' '.$attr.' page="' . ($this->page - 1).'"' : "";
                $html .= '<a class="item ' . $class . '" ' . $href.'> ← </a>';


                if ($start > 1) {
                    $html .= '<a class="item"  '.$attr.' page="1">1</a>';
                    $html .= '<a class="disabled item">...</a>';
                }

                for ($i = $start ; $i <= $end; $i++) {
                    $class  = $this->page == $i ? "active" : "";
                    $html .= '<a class="item '.$class.'"  '.$attr.' page="' . $i . '">' . $i . '</a>';
                }

                if ($end < $last) {
                    $html .= '<a class="disabled item">...</a>';
                    $html .= '<a class="item"   '.$attr.' page="' . $last . '">' . $last . '</a>';
                }

                $class = ($this->page >= $last) ? "disabled" : "";
                $href = ($this->page < $last) ? ' '.$attr.' page="' . ($this->page + 1).'"' : "";
                $html .= '<a class="item ' . $class . '" ' . $href.'> → </a>';

                $html .= '</div>';

                /*** mobile pagination ***/
                $html .= "<div class='".$list_class." mobile'>";

                $class = ($this->page <= 1) ? "disabled" : "";
                $href = ($this->page > 1) ? ' '.$attr.' page="' . ($this->page - 1).'"' : "";
                $html .= '<a class="item ' . $class . '" ' . $href.'> ← </a>';

                $html .= "<a class='active item' href='' ".$attr." page='".$this->page."'>(".$this->page."/".$last.")</a>";

                $class = ($this->page >= $last) ? "disabled" : "";
                $href = ($this->page < $last) ? ' '.$attr.' page="' . ($this->page + 1).'"' : "";
                $html .= '<a class="item ' . $class . '" ' . $href.'> → </a>';

                $html .= "</div>";
                break;
        }
        return $html;
    }

    public function getTotal()
    {
        return $this->total;
    }

}	/** End of Class **/
