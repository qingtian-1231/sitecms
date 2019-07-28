<?php

namespace woo\model\traits;

trait WooCURD
{
    /**
     * 插入数据
     */
    public function createData(array $data = [], array $options = [])
    {
        if (empty($data)) {
            $data = helper('Form')->data[$this->name];
        }
        if (empty($data)) {
            $data = input('post.');
        }
        if (empty($data)) {
            exception('未获取到新增数据');
        }

        $allowField = isset($options['allowField']) ? $options['allowField'] : true;
        return $this->isUpdate(false)->allowField($allowField)->save($data);
    }

    /**
     * 删除数据 根据主键删除（一个或数组多个） 支持额外条件
     * 返回一个数组 ： select_count 需要删除的条数； delete_count 删除成功的条数；delete_list 删除数据的相关数据
     */
    public function deleteData($id, array $where = [])
    {
        $pk = $this->getPk();
        if (is_numeric($id)) {
            $id = [intval($id)];
        } elseif (is_string($id)) {
            $id = explode(',', $id);
        }

        if (!is_array($id)) {
            settype($id, 'array');
        }

        $delObj = $this->where($where)->where($pk, 'IN', $id)->select();
        // 需要删除的条数
        $selectCount = count($id);
        $deleteId = [];

        foreach ($delObj as $item) {
            $deleteId[] = $item[$pk];
        }
        $deleteList = $delObj->toArray();
        // 执行主键删除返回删除条数
        $this->destroy($deleteId);
        return [
            'delete_list' => $deleteList,
            'select_count' => $selectCount,
            'delete_count' => count($deleteList)
        ];
    }


    /**
     * 修改数据
     */
    public function modifyData($id, array $data = [], array $options = [])
    {
        $pk = $this->getPk();
        if (empty($data)) {
            $data = helper('Form')->data[$this->name];
        }
        if (empty($data)) {
            $data = input('post.');
        }
        if (empty($data)) {
            exception('未获取到更新数据');
        }

        if (!$id) {
            exception('未获取到更新数据的ID');
        }
        $data[$pk] = $id;
        // 修改前额外条件判断 由于模型更新不方便直接自定义条件 所有有自定义条件 先查询
        if (!empty($options['where'])) {
            $exists = $this->where($pk, '=', $id)->where($options['where'])->find();
            // 自定义条件以后 数据不存在 修改失败
            if (empty($exists)) {
                return false;
            }
        }
        $allowField = isset($options['allowField']) ? $options['allowField'] : true;
        $this->isUpdate(true)->force()->allowField($allowField)->save($data, [$pk => $id]);
        return empty($this->error) ? true : false;
    }

    /**
     * 数据分页
     */
    public function getPageList($options = [])
    {
        $options = $options + [
                'where' => [],
                'order' => [],
                'with' => [],
                'whereOr' => [],
                'whereColumn' => [],
                'whereTime' => [],
                'field' => true,
                'limit' => 15,
                'paginate' => []
                // paginate 下的参数：
                /**
                 * list_rows    每页数量
                 * page    当前页
                 * path    url路径
                 * query    url额外参数
                 * fragment    url锚点
                 * var_page    分页变量
                 * type    分页类名
                 * simple    简洁分页
                 * total    总条数
                 */
            ];
        $pk = $this->getPk();

        if (isset($options['paginate']['simple'])) {
            $simple = $options['paginate']['simple'];
        } else {
            $simple = false;
        }

        if (!$simple && isset($options['paginate']['total'])) {
            $simple = intval($options['paginate']['total']);
        }


        if (isset($this->form['list_order']) && !isset($options['order']['list_order'])) {
            $options['order']['list_order'] = 'DESC';
        }

        if (isset($this->form[$pk]) && !isset($options['order'][$pk])) {
            $options['order'][$pk] = 'DESC';
        }

        //unset($options['order']['list_order']);

        $list = $this
            ->with($options['with'])
            ->where($options['where'])
            ->whereOr($options['whereOr'])
            ->whereColumn($options['whereColumn'])
            ->where(function ($query) use ($options) {
                if (!empty($options['whereTime'])) {
                    $query->whereTime(...$options['whereTime']);
                }
            })
            ->field($options['field'])
            ->order($options['order'])
            ->paginate(intval($options['limit']), $simple, $options['paginate']);
        $render = $list->render();
        $list = $list->toArray();
        $pageList = $list['data'];
        unset($list['data']);
        $page = $list;

        return [
            'render' => $render,
            'page' => $page,
            'list' => $pageList
        ];
    }
}
