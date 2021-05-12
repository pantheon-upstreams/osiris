# OSIRIS

The judge of the living and the dead.

## Requirements

- Terminus authenticated on a Pantheon employee account that has access to the community "CI Fixtures for Projects"
  [ ORG_ID: 5ae1fa30-8cc4-4894-8ca9-d50628dcba17 ]

- Composer [ TODO: researching using a docker command to execute ]

## Usages

from the root directory:

```composer ensure {PANTHEON_SITE}```

- Creates {PANTHEON_SITE} if it does not currently exist

- Ensures there's a vXX multi-dev site for every version of PHP we support

- Creates the dashboard page in the dev environment
