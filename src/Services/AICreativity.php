<?php

namespace Daavelar\LaravelAI\Services;

enum AICreativity: float
{
    case LOW = 0.2;
    case MEDIUM = 0.5;
    case HIGH = 0.8;
    case VERY_HIGH = 1.0;
    case EXTREME = 1.5;
} 