<?php

namespace alcamo\binary_data;

/**
 * @brief Immutable binary content
 *
 * @invariant Immutable class.
 *
 * @date Last reviewed 2025-10-08
 */
class ImmutableBinaryString extends AbstractBinaryString
{
    use PreventWriteArrayAccessTrait;
}
