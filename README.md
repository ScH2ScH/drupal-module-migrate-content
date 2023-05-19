# Migrate Content Module

## Description
The Migrate Content module is designed to facilitate the transfer of selected content from one Drupal site to another. It allows you to migrate content from a staging site to a production site, helping you streamline your content deployment process.

## Requirements
- Drupal 9 or later for the staging server
- Drupal 9 or later with Restful Web Services and HTTP Basic Authentication installed on the production server

## Installation
1. Download the module and place it in the modules directory of your Drupal installation.
2. Enable the Migrate Content module through the Drupal administration interface or by using Drush.
3. Make sure that Restful Web Services and HTTP Basic Authentication modules are enabled on the production server.

## Configuration
1. On the production server, enable the following REST resources:
    - Content: GET, POST, PATCH
    - Content Type: GET
      Configure the authentication method to use basic authentication.

## Limitations
- The module does not check if the UUID of the content already exists on the production server, so update doesn't work. Couldn't figure out how to fetch node using Uuid, and nid is useless in this case.
- Content types on the staging and production servers are not validated for compatibility during the migration.
- Related files associated with the content are not transferred by this module.
- Large migrations may take a significant amount of time. Consider implementing a queuing system to avoid blocking content editors.
- The module does not currently provide a reports page to track the status of content migration. It does not display information on which content has been successfully migrated and which content is pending migration. This feature can be considered for future enhancements.
- The module does not include a menu item to directly access the Connect form. Users will need to manually enter the URL or create a custom menu link to navigate to the Connect form. I've tried to creat it through the code, but I failed, Repeatedly

## Usage
1. Log in to the staging site administration interface.
2. Navigate to the "Content" page (/admin/content) and search for the content you want to migrate.
3. Select the checkboxes next to the desired content items.
4. Choose "Migrate Content" from the action dropdown menu.
5. If you are not already logged in to the destination Drupal server (production), you will be prompted to authenticate. Click on the provided link to log in.
6. Confirm the migration to initiate the transfer of the selected content to the production site.

## Sample Configurations and Databases

Sample configurations and databases exported from the staging and production servers are provided in the "additional-data" folder. These files can serve as examples to configure your staging and production environments accordingly. You can find the necessary configuration files and database dumps inside the "additional-data" folder.
Please ensure to review and adapt these sample configurations to match your specific staging and production setups.

## Troubleshooting
- If you encounter any issues during the migration process, please refer to the Drupal documentation or seek assistance from the Drupal community. I won't be of much help, yet

## Contributing
Contributions to the Migrate Content module are welcome! If you discover any bugs, have feature requests, or would like to contribute code improvements, please submit an issue or pull request on the module's GitHub repository.

## License
This module is licensed under the [GNU General Public License (GPL)](https://www.gnu.org/licenses/gpl-2.0.html). See the LICENSE file for more details.
