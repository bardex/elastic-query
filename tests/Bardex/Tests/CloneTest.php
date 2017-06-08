<?php

namespace Bardex\Tests;


class CloneTest extends AbstractTestCase
{
    public function testClone()
    {
        $query = $this->createQuery();
        $query->where('id')->greater(0)
              ->select(['id'])
              ->setOrderBy('id');

        $fork = $query->fork();

        // копия и исходный объект - разные экземпляры
        $this->assertFalse($query === $fork);

        // whereHelper тоже должен быть разный для каждого объекта
        $this->assertFalse($query->where('id') === $fork->where('id'));

        // но параметры elastic-запроса должны быть одинаковые, по какой-то из объектов не был изменен
        $this->assertEquals($query->getQuery(), $fork->getQuery());
        $query->where('status')->equal(0);
        $this->assertNotEquals($query->getQuery(), $fork->getQuery());
    }
}
