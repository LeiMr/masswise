<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'member';
    /**
     * 关联数据表主键
     *
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * 是否Laravel 自动管理的数据列
     *
     * @var string
     */
    public $timestamps = false;
}
