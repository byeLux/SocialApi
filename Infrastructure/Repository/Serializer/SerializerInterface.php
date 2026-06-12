<?php

interface SerializerInterface {
    //public function serialize(object $entity): array;
    public function deserialize(array $result): ?object;
}