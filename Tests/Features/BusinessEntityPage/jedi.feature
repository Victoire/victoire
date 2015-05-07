@mink:selenium2 @alice(Page) @alice(User) @reset-schema
Feature: Manage jedis

    Background:
        Given I am logged in as "anakin@victoire.io"
        And I maximize the window

    Scenario: I can list jedis
        Given the following Jedis:
            | name   | side   | midiChlorians | slug   |
            | Anakin | dark   | 20000         | anakin |
            | Yoda   | bright | 17500         | yoda   |
        And I open the hamburger menu
        Then I should see "Jedi"
        When I follow "Jedi"
        Then I should be on "/victoire-dcms/backend/jedi/"
        Then I should see "Liste des Jedis"
        Then I should see the following table
            | Nom    | Medichloriens | Côté de la force |
            | Anakin | 20000         | dark             |
            | Yoda   | 17500         | bright           |

    Scenario: I try to list jedis but there is no results
        Given I am on "/victoire-dcms/backend/jedi/"
        Then I should see "Aucun résultat"

    Scenario: I create a new jedi
        Given I am on "/victoire-dcms/backend/jedi/"
        Then I should see "Aucun résultat"
        When I follow "Nouveau jedi"
        Then I should be on "/victoire-dcms/backend/jedi/new"
        And I should see "Nouveau Jedi"
        When I fill in "Nom" with "Anakin"
        And I fill in "MediChloriens" with "20000"
        And I fill in "Identifiant" with "anakin"
        And I select "Obscure" from "Coté de la force"
        And I press "Créer"
        Then I should be on "/victoire-dcms/backend/jedi/"
        Then I should see "Liste des Jedis"
        Then I should see the following table
          | Nom    | Medichloriens | Côté de la force |
          | Anakin | 20000         | dark             |

    Scenario: I delete a jedi
        Given the following Jedis:
          | name   | side   | midiChlorians | slug   |
          | Anakin | dark   | 20000         | anakin |
          | Yoda   | bright | 17500         | yoda   |
        And I am on "/victoire-dcms/backend/jedi/"
        Then I should see the following table
          | Nom    | Medichloriens | Côté de la force |
          | Anakin | 20000         | dark             |
          | Yoda   | 17500         | bright           |
        And I follow the 1st "Modifier" link
        Then I should see "Modification de Jedi"
        When I press "Supprimer"
        Then I should be on "/victoire-dcms/backend/jedi/"
        Then I should see "Liste des Jedis"
        Then I should see the following table
            | Nom    | Medichloriens | Côté de la force |
            | Yoda   | 17500         | bright           |

