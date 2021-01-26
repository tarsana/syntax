<?php namespace Tarsana\Syntax;

function string(): StringSyntax {
  return StringSyntax::instance();
}

function boolean(): BooleanSyntax {
  return BooleanSyntax::instance();
}

function number(): NumberSyntax {
  return NumberSyntax::instance();
}

function arr(Syntax $syntax = null, string $separator = null): ArraySyntax {
  return new ArraySyntax($syntax, $separator);
}

function object(array $fields, string $separator = null): ObjectSyntax {
  return new ObjectSyntax($fields, $separator);
}

function optional(Syntax $syntax, $default): OptionalSyntax {
  return new OptionalSyntax($syntax, $default);
}

function syntax(): SyntaxSyntax {
  return SyntaxSyntax::instance();
}
