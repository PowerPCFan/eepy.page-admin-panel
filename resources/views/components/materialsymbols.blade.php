@props([
  'icon',
  'class' => '',
  'style' => 'outlined',
  'fill' => '',
  'size' => '24px',
  'disabletranslatealignmentfix' => '',
])

@php
  $iconCleaned = str_replace('-', '_', $icon);
  $styleToPrefix = ["outlined" => "gmso", "rounded" => "gmsr", "sharp" => "gmss"];
  $prefix = $styleToPrefix[$style] ?? "gmso";
  $suffix = $fill === 'true' ? "-fill" : "";
  $defaultClasses = "mr-1.5 -translate-y-[1px]";
  if ($disabletranslatealignmentfix === "true") {
    $defaultClasses = str_replace(" -translate-y-[1px]", "", $defaultClasses);
  }
@endphp

@svg("{$prefix}-{$iconCleaned}{$suffix}", [
  'class' => "{$defaultClasses} {$class}",
  'fill' => "currentColor",
  'height' => "{$size}",
  'width' => "{$size}",
])
