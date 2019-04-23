<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/21
 * Time: 下午3:26
 */

namespace classes;

use app\master\model\MasterModel;
use app\Substation\model\SubstationModel;

class AdminClass extends FirstClass
{
    private $master = null;

    public function master()
    {
        if (is_null($this->master)) {
            $master = session('master');
            $model = new MasterModel();
            $this->master = $model->where('id', '=', $master['id'])->find();
        }
        return $this->master;
    }

    public function is_substation()
    {
        if (SUBSTATION == '0') return '1';

        $substation = new SubstationModel();
        $substation = $substation->where('id', '=', SUBSTATION)->find();

        if ($substation['pid'] == '0') return '1';

        return '0';
    }

    public function substation_ids()
    {
        $substation = request()->get('the_substation');

        if (!empty($substation)) {

            $substation = [$substation];
        } else {

            $sub = new SubstationModel();
            $substation = $sub->where('id', '=', SUBSTATION)
                ->whereOr('pid', '=', SUBSTATION)
                ->whereOr('top', '=', SUBSTATION)
                ->column('id');
        }

        return $substation;
    }

    public function my_substation()
    {
        $sub = new SubstationModel();
        $substation = $sub->where('id', '=', SUBSTATION)
            ->whereOr('pid', '=', SUBSTATION)
            ->whereOr('top', '=', SUBSTATION)
            ->column('id');

        return $substation;
    }
}