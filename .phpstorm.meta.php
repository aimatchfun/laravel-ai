<?php

namespace PHPSTORM_META {

    override(
        \AIMatchFun\LaravelAI\Facades\AI::provider(0),
        map([
            '' => \AIMatchFun\LaravelAI\Services\AIService::class,
        ])
    );

    override(
        \AIMatchFun\LaravelAI\Facades\AI::model(0),
        map([
            '' => \AIMatchFun\LaravelAI\Services\AIService::class,
        ])
    );

    override(
        \AIMatchFun\LaravelAI\Facades\AI::systemInstruction(0),
        map([
            '' => \AIMatchFun\LaravelAI\Services\AIService::class,
        ])
    );

    override(
        \AIMatchFun\LaravelAI\Facades\AI::prompt(0),
        map([
            '' => \AIMatchFun\LaravelAI\Services\AIService::class,
        ])
    );

    override(
        \AIMatchFun\LaravelAI\Facades\AI::temperature(0),
        map([
            '' => \AIMatchFun\LaravelAI\Services\AIService::class,
        ])
    );

    override(
        \AIMatchFun\LaravelAI\Facades\AI::stream(0),
        map([
            '' => \AIMatchFun\LaravelAI\Services\AIService::class,
        ])
    );

    override(
        \AIMatchFun\LaravelAI\Facades\AI::responseFormat(0),
        map([
            '' => \AIMatchFun\LaravelAI\Services\AIService::class,
        ])
    );

    override(
        \AIMatchFun\LaravelAI\Facades\AI::previewMessages(0),
        map([
            '' => \AIMatchFun\LaravelAI\Services\AIService::class,
        ])
    );
}

