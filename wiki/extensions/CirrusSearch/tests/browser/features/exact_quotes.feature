Feature: Searches that contain quotes
  Background:
    Given I am at a random page

  @setup_main @exact_quotes
  Scenario: Searching for a word in quotes disbles stemming (can't find plural with singular)
    When I search for "pickle"
    Then there are no search results
    And there is no link to create a new page from the search result

  @setup_main @exact_quotes
  Scenario: Searching for a word in quotes disbles stemming (can still find plural with exact match)
    When I search for "pickles"
    Then Two Words is the first search result

  @setup_main @exact_quotes
  Scenario: Searching for a phrase in quotes disbles stemming (can't find plural with singular)
    When I search for "catapult pickle"
    Then there are no search results

  @setup_main @exact_quotes
  Scenario: Searching for a phrase in quotes disbles stemming (can still find plural with exact match)
    When I search for "catapult pickles"
    Then Two Words is the first search result

  @exact_quotes
  Scenario: Quoted phrases match stop words
    When I search for "Contains A Stop Word"
    Then Contains A Stop Word is the first search result

  @setup_main @exact_quotes
  Scenario: Adding a ~ to a phrase keeps stemming enabled
    When I search for "catapult pickle"~
    Then Two Words is the first search result

  @exact_quotes
  Scenario: Adding a ~ to a phrase stops it from matching stop words
    When I search for "doesn't actually Contain A Stop Words"~
    Then Doesn't Actually Contain Stop Words is the first search result

  @setup_main @exact_quotes
  Scenario: Adding a ~<a number>~ to a phrase keeps stemming enabled
    When I search for "catapult pickle"~0~
    Then Two Words is the first search result

  @setup_main @exact_quotes
  Scenario: Adding a ~<a number> to a phrase turns off because it is a proximity search
    When I search for "catapult pickle"~0
    Then there are no search results

  @exact_quotes
  Scenario: Searching for a quoted * actually searches for a *
    When I search for "pick*"
    Then Pick* is the first search result

  @exact_quotes @setup_main
  Scenario Outline: Searching for "<word> <word>"~<number> activates a proximity search
    When I search for "ffnonesenseword anotherword"~<proximity>
    Then <result> is the first search result
  Examples:
    | proximity | result    |
    | 0         | none      |
    | 1         | none      |
    | 2         | Two Words |
    | 3         | Two Words |
    | 77        | Two Words |
