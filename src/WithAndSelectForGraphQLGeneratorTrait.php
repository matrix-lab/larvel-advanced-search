<?php

namespace MatrixLab\LaravelAdvancedSearch;

use ReflectionClass;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

trait WithAndSelectForGraphQLGeneratorTrait
{
    public function getAllColumns()
    {
        $allColumns = (new static())->allColumns;

        if(empty($allColumns)) {
            throw new InternalErrorException(" SQL 的 select 内容为空，请检查 ".static::class." 中是否有 \$allColumns 字段，如果没有，请执行 php artisan make:models-columns 生成。");
        }

        return $allColumns;
    }

    public static function getWithAndSelect($info)
    {
        $fields = $info->getFieldSelection(5);

        return static::parseResolveInfoToWithColumns($fields);
    }

    /**
     * 解析 with 关系和 selects 关系
     *
     * @param $fields
     * @return array
     * @throws \ReflectionException
     */
    protected static function parseResolveInfoToWithColumns($fields)
    {
        $fields = isset($fields['items']) ? $fields['items'] : $fields;

        $columns         = [];
        $withes          = [];
        $modelReflection = new ReflectionClass(static::class);
        foreach ($fields as $field => $isSingleField) {
            if (static::canBeSelected((new static), $field)) {
                $columns[] = $field;
            } elseif ($modelReflection->hasMethod($field)) {

                //  尝试构建递归
                //
                //  $withes[] = $field.':'.join(',', static::getRelationSelect($field, static::parseResolveInfoToWithColumns($isSingleField)[1]));

                //  list($subWith, $subSelect) = static::parseResolveInfoToWithColumns($isSingleField);
                //
                //  foreach ($subWith as $with) {
                //      $withes[] = $field.'.'.$with;
                //  }

                // 目前支持嵌套3层查询

                $relation = (new static)->{$field}(); // 关联模型对象
                /** @var Model $relation */
                $relationModel      = $relation->getModel();
                $relationReflection = new ReflectionClass($relationModel); // 关联模型对象的反射
                $withColumns        = [];
                foreach ($isSingleField as $subField => $isSingleSubField) {
                    if (static::canBeSelected($relationModel, $subField)) {
                        $withColumns[] = $subField;
                    } elseif ($relationReflection->hasMethod($subField)) {
                        $withes[] = $field.'.'.$subField.':'.join(',', static::parseResolveInfoToWithColumns($isSingleSubField)[1]);
                    }
                }

                $withes[] = $field.':'.join(',', static::getRelationSelect($field, $withColumns));
            }
        }


        return [$withes, $columns];
    }

    private static function canBeSelected($model, $field)
    {
        return in_array($field, $model->getAllColumns());
    }

    /**
     * 获取关联关系里面的 select
     *
     * @param $relation
     * @param $columns
     * @return array
     * @throws \ReflectionException
     */
    private static function getRelationSelect($relation, $columns)
    {
        $relation = (new static)->{$relation}();

        if ((new ReflectionClass($relation))->hasMethod('getModel')) {
            $relationModel = $relation->getModel();

            return array_intersect($columns, $relationModel->getAllColumns());
        }

        return $columns;
    }
}
