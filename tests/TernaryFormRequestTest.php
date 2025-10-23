<?php

namespace VinkiusLabs\Trilean\Tests;

use Orchestra\Testbench\TestCase;
use VinkiusLabs\Trilean\Enums\TernaryState;
use VinkiusLabs\Trilean\Support\FormRequests\TernaryFormRequest;
use VinkiusLabs\Trilean\TernaryLogicServiceProvider;

class TernaryFormRequestTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TernaryLogicServiceProvider::class];
    }

    public function test_helper_methods_transform_input(): void
    {
        $request = new class extends TernaryFormRequest {
            public array $validatedData = [];

            public function rules(): array
            {
                return [];
            }

            public function validated($key = null, $default = null): array
            {
                return $this->validatedData;
            }

            public function setTestData(array $input, array $validated = []): void
            {
                $this->merge($input);
                $this->validatedData = $validated;
            }
        };

        $request->setContainer($this->app);
        $request->setRedirector($this->app['redirect']);
        $request->setTestData([
            'alpha' => true,
            'beta' => false,
            'gamma' => null,
        ]);

        $this->assertTrue($request->ternary('alpha')->isTrue());
        $this->assertTrue($request->ternaryAny(['alpha', 'gamma']));
        $this->assertFalse($request->ternaryAll(['alpha', 'beta']));

        $gate = $request->ternaryGate(['alpha', 'beta'], [
            'operator' => 'weighted',
            'weights' => [2, -1],
        ]);

        $this->assertInstanceOf(TernaryState::class, $gate);
        $this->assertTrue($gate->isTrue());
    }
}
