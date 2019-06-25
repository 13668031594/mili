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
use classes\system\BankClass;
use classes\system\SystemClass;
use classes\vendor\StorageClass;

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

        if ($substation == 'all') {

            $sub = new SubstationModel();
            $sub = $sub->where('id', '=', SUBSTATION)
                ->whereOr('pid', '=', SUBSTATION)
                ->whereOr('top', '=', SUBSTATION)
                ->column('id');

            if (SUBSTATION == '0') $sub[] = '0';

        } elseif (!is_null($substation)) {

            $sub = $substation;
        } else {

            if (SUBSTATION != '0') {
                $sub = new SubstationModel();
                $sub = $sub->where('id', '=', SUBSTATION)->column('id');
            } else {

                $sub = [0];
            }
        }

        return $sub;
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

    //判断上级站是否开启优云宝支付
    public function youyunbao()
    {
        if (SUBSTATION == 0) return false;

        $substation = new SubstationModel();
        $substation = $substation->find(SUBSTATION);

        if (!empty($substation->pid)) {

            $name = 'sysSetting_' . $substation->pid . '.txt';
        } else {

            $name = 'sysSetting.txt';
        }

        $class = new SystemClass();
        $class->storage = new StorageClass($name);
        $set = $class->index();

        if ($set['youSwitch'] == 'on') return true;
        else return false;
    }

    //判断上级站是否开启优云宝支付
    public function bank()
    {
        if (SUBSTATION == 0) return false;

        $substation = new SubstationModel();
        $substation = $substation->find(SUBSTATION);

        if (!empty($substation->pid)) {

            $name = 'bankSetting_' . $substation->pid . '.txt';
        } else {

            $name = 'bankSetting.txt';
        }

        $class = new BankClass();
        $class->storage = new StorageClass($name);
        $set = $class->index();

        if ($set['switch'] == 'on') return true;
        else return false;
    }
}