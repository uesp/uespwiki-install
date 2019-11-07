@chrome @en.m.wikipedia.beta.wmflabs.org @firefox @test2.m.wikipedia.org @vagrant
Feature: Search

  Background:
    Given I am using the mobile site
      And the page "Selenium search test" exists
      And I am on the "Main Page" page
    When I click the placeholder search box

  Scenario: Closing search (overlay button)
    When I click the search overlay close button
    Then I should not see the search overlay

  Scenario: Closing search (browser button)
    When I click the browser back button
    Then I should not see the search overlay

  @smoke @integration
  Scenario: Search for partial text
    When I type into search box "Selenium search tes"
    Then search results should contain "Selenium search test"

  Scenario: Search with search in pages button
      And I see the search overlay
      And I type into search box "Test is used by Selenium web driver"
      And I see the search in pages button
      And I click the search in pages button
    Then I should see a list of search results

  Scenario: Search with enter key
      And I see the search overlay
      And I type into search box "Test is used by Selenium web driver"
      And I press the enter key
    Then I should see a list of search results

  Scenario: Going back to the previous page
    When I type into search box "Selenium search tes"
    When I click a search result
    When I click the browser back button
    Then I should not see '#/search' in URL

  Scenario: Clicking on a watchstar toggles the watchstar
    Given I am logged into the mobile website
      And the page "Selenium search test" exists
    When I click the placeholder search box
      And I type into search box "Selenium search tes"
      And I click a search watch star
    Then I should see a toast
