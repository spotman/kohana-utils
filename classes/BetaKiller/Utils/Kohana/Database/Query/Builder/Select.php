<?php
namespace BetaKiller\Utils\Kohana\Database\Query\Builder;

class Select extends \Kohana_Database_Query_Builder_Select {
    /**
     * Кеширует результаты запроса
     * Можно явно указать время жизни в секундах.
     * Можно указать строковый ключ и тогда TTL будет взят из конфига, где ключом является имя первой переданной в метод from() таблицы + строковый ключ, явно определяющий запрос
     * Можно не указывать ничего и тогда будет взят дефолтный TTL для таблицы
     *
     * @param int|string|null $lifetime Время жизни кеша в секундах или строковый ключ для запроса или NULL для дефолтного кеширования на 60 секунд
     * @param bool $force
     * @return $this
     * @throws \Kohana_Exception
     */
    public function cached($lifetime = NULL, $force = FALSE)
    {
        if (\Kohana::$environment !== \Kohana::PRODUCTION) {
            return $this;
        }

        $key = \Arr::get($this->_from, 0);

        if (\is_string($lifetime)) {
            $key .= '.'.$lifetime;
        }

        if (!$lifetime || \is_string($lifetime))
        {
            $group = \Kohana::$config->load('clt');
            $lifetime = $group ? $group->get($key, 60) : 60;
        }

        return parent::cached($lifetime, $force);
    }
}
