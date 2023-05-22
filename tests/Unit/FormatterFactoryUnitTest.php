<?php

namespace AlexSkrypnyk\Tests\Unit;

use AlexSkrypnyk\ShellVariablesExtractor\Formatter\CsvFormatter;
use AlexSkrypnyk\ShellVariablesExtractor\Formatter\DummyFormatter;
use AlexSkrypnyk\ShellVariablesExtractor\Formatter\FormatterFactory;
use AlexSkrypnyk\ShellVariablesExtractor\Formatter\MarkdownBlocksFormatter;
use AlexSkrypnyk\ShellVariablesExtractor\Formatter\MarkdownTableFormatter;

/**
 * Class FormatterFactoryUnitTest.
 *
 * Unit tests for the FormatterFactory class.
 */
class FormatterFactoryUnitTest extends UnitTestBase {

  /**
   * Test that the formatter factory can create a formatter.
   */
  public function testCreate() {
    $formatter = FormatterFactory::create('csv', [], []);
    $this->assertNotNull($formatter);
    $this->assertEquals('Name,"Default value",Description' . PHP_EOL, $formatter->format());
    FormatterFactory::reset();
  }

  /**
   * Test that the formatter factory can discover own and custom formatters.
   */
  public function testDiscoverOwnFormatters() {
    $mock = $this->prepareMock(FormatterFactory::class);

    $this->assertEquals([], $this->getProtectedValue($mock, 'formatters'));
    $this->callProtectedMethod($mock, 'discoverOwnFormatters');
    $this->assertEquals([
      'csv' => CsvFormatter::class,
      'md-table' => MarkdownTableFormatter::class,
      'md-blocks' => MarkdownBlocksFormatter::class,
    ], $this->getProtectedValue($mock, 'formatters'));
    FormatterFactory::reset();
    $this->assertEquals([], $this->getProtectedValue($mock, 'formatters'));

    $mock = $this->prepareMock(FormatterFactory::class);
    $this->assertEquals([], $this->getProtectedValue($mock, 'formatters'));
    $this->callProtectedMethod($mock, 'discoverOwnFormatters', [$this->fixtureDir()]);
    $this->assertEquals(['dummy' => DummyFormatter::class], $this->getProtectedValue($mock, 'formatters'));
  }

  /**
   * Test that an exception is thrown when an invalid formatter is requested.
   */
  public function testException() {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Invalid formatter: non-existent');
    FormatterFactory::create('non-existent', [], []);
  }

}
