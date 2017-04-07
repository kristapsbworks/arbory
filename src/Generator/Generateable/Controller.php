<?php

namespace CubeSystems\Leaf\Generator\Generateable;

use CubeSystems\Leaf\Generator\Generateable\Extras\Field;
use CubeSystems\Leaf\Generator\GeneratorFormatter;
use CubeSystems\Leaf\Generator\Schema;
use CubeSystems\Leaf\Generator\Stubable;
use CubeSystems\Leaf\Generator\StubGenerator;
use CubeSystems\Leaf\Services\StubRegistry;
use Illuminate\Console\DetectsApplicationNamespace;
use Illuminate\Filesystem\Filesystem;

class Controller extends StubGenerator implements Stubable
{
    use GeneratorFormatter, DetectsApplicationNamespace;

    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @param StubRegistry $stubRegistry
     * @param Filesystem $filesystem
     * @param Schema $schema
     */
    public function __construct(
        StubRegistry $stubRegistry,
        Filesystem $filesystem,
        Schema $schema
    )
    {
        $this->stub = $stubRegistry->findByName( 'controller' );
        $this->filesystem = $filesystem;
        $this->schema = $schema;
    }

    /**
     * @return void
     */
    public function generate()
    {
        parent::generate();

        $this->registerConfigModule();
    }

    /**
     * @return void
     */
    protected function registerConfigModule()
    {
    }

    /**
     * @return string
     */
    public function getCompiledControllerStub(): string
    {
        $viewFields = (clone $this->schema->getFields())->transform( function( $field ) {
            /**
             * @var Field $field
             */
            return sprintf(
                '\'%1$s\' => $node->%1$s,',
                snake_case( $field->getName() )
            );
        } );

        $replace = [
            '{{namespace}}' => $this->getNamespace(),
            '{{className}}' => $this->getClassName(),
            '{{viewPath}}' => 'controllers.' . snake_case( $this->schema->getName() ) . '.index',
            '{{viewFields}}' => $this->prependSpacing( $viewFields, 3 )->implode( PHP_EOL ),
        ];

        return str_replace(
            array_keys( $replace ),
            array_values( $replace ),
            $this->stub->getContents()
        );
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className( $this->schema->getName() ) . 'PageController';
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->getClassName() .'.php';
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->getAppNamespace() . 'Http\Controllers';
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return app_path( 'Http/Controllers/' . $this->getFilename() );
    }
}