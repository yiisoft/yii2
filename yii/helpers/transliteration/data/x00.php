<?php

$base = [
  // Note: to save memory plain ASCII mappings have been left out.
  0x80 => '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
  0x90 => '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
  0xA0 => ' ', '!', 'C/', 'PS', '$?', 'Y=', '|', 'SS', '"', '(c)', 'a', '<<', '!', '', '(r)', '-',
  0xB0 => 'deg', '+-', '2', '3', '\'', 'u', 'P', '*', ',', '1', 'o', '>>', '1/4', '1/2', '3/4', '?',
  0xC0 => 'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I',
  0xD0 => 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'x', 'O', 'U', 'U', 'U', 'U', 'Y', 'Th', 'ss',
  0xE0 => 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i',
  0xF0 => 'd', 'n', 'o', 'o', 'o', 'o', 'o', '/', 'o', 'u', 'u', 'u', 'u', 'y', 'th', 'y',
];

// Overrides for Danish input.
$variant['da'] = [
  0xC5 => 'Aa',
  0xD8 => 'Oe',
  0xE5 => 'aa',
  0xF8 => 'oe',
];

// Overrides for German input.
$variant['de'] = [
  0xC4 => 'Ae',
  0xD6 => 'Oe',
  0xDC => 'Ue',
  0xE4 => 'ae',
  0xF6 => 'oe',
  0xFC => 'ue',
  0xDF => 'ss',
];

// Overrides for Spanish input.
$variant['es'] = [
  0xE1 => 'a',
  0xE9 => 'e',
  0xED => 'i',
  0xF3 => 'o',
  0xFA => 'u',
  0xF1 => 'n',
];
