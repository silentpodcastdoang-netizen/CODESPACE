# CODESPACE Data Models and Storage Schema

## 1. Overview

This document defines the complete data structures, file formats, and storage schema for the CODESPACE CLI tool. It covers templates, user configurations, snippets, project metadata, and CLI state management.

## 2. Storage Architecture

### 2.1 Directory Structure
```
.codespace/
├── templates/                    # Template definitions and files
│   ├── registry.json            # Master template registry
│   ├── nodejs/                  # Node.js templates
│   │   ├── express/             # Express.js template
│   │   │   ├── template.json    # Template definition
│   │   │   ├── files/           # Template files
│   │   │   │   ├── package.json.hbs
│   │   │   │   ├── src/
│   │   │   │   │   └── index.js.hbs
│   │   │   │   └── README.md.hbs
│   │   │   └── config/          # Template-specific configs
│   │   │       └── gitignore.hbs
│   │   └── vanilla/             # Vanilla Node.js template
│   ├── python/                  # Python templates
│   │   ├── flask/
│   │   └── django/
│   ├── java/                    # Java templates
│   │   ├── maven/
│   │   └── gradle/
│   ├── go/                      # Go templates
│   └── shared/                  # Shared template resources
│       ├── partials/            # Handlebars partials
│       └── helpers/             # Custom Handlebars helpers
├── snippets/                    # Code snippets storage
│   ├── index.json              # Snippets master index
│   ├── by-language/            # Organized by language
│   │   ├── javascript/
│   │   │   ├── http-endpoint.json
│   │   │   ├── auth-handler.json
│   │   │   └── database-query.json
│   │   ├── python/
│   │   └── java/
│   └── by-tag/                  # Symbolic links by tags
│       ├── api/
│       ├── auth/
│       └── database/
├── config/                      # User configurations
│   ├── user.json               # Main user configuration
│   ├── templates.json          # Custom template overrides
│   ├── ci-presets.json         # CI/CD workflow presets
│   └── licenses.json           # Custom license templates
├── cache/                       # Temporary cache
│   ├── template-cache.json     # Rendered template cache
│   ├── snippets-cache.json     # Snippet search cache
│   └── metadata/               # Cached metadata
│       ├── npm-stats.json
│       └── github-stats.json
├── logs/                       # Application logs
│   ├── operations.log          # Operation history
│   ├── errors.log              # Error logs
│   └── performance.log         # Performance metrics
└── backups/                    # Configuration backups
    ├── user-config-backups/
    └── snippet-backups/
```

## 3. Template Data Models

### 3.1 Template Registry Schema
```json
{
  "$schema": "https://codespace.dev/schemas/template-registry.json",
  "version": "1.0.0",
  "lastUpdated": "2024-01-01T00:00:00Z",
  "templates": [
    {
      "id": "nodejs-express",
      "name": "Express.js Web Application",
      "description": "Full-featured Express.js web application with middleware and routing",
      "category": "web-framework",
      "language": "javascript",
      "framework": "express",
      "version": "1.2.0",
      "author": "CODESPACE Team",
      "maintainer": "codespace@example.com",
      "license": "MIT",
      "tags": ["web", "api", "express", "rest", "middleware"],
      "minNodeVersion": "14.0.0",
      "maxNodeVersion": "20.0.0",
      "supportedPlatforms": ["linux", "macos", "windows"],
      "templatePath": "templates/nodejs/express/",
      "dependencies": [
        {
          "name": "express",
          "version": "^4.18.0",
          "type": "production",
          "description": "Fast, unopinionated web framework"
        },
        {
          "name": "cors",
          "version": "^2.8.5",
          "type": "production",
          "description": "Enable CORS for Express"
        },
        {
          "name": "helmet",
          "version": "^7.0.0",
          "type": "production",
          "description": "Security middleware"
        }
      ],
      "devDependencies": [
        {
          "name": "nodemon",
          "version": "^3.0.0",
          "type": "development",
          "description": "Auto-restart on file changes"
        },
        {
          "name": "jest",
          "version": "^29.0.0",
          "type": "development",
          "description": "Testing framework"
        }
      ],
      "scripts": {
        "start": "node src/index.js",
        "dev": "nodemon src/index.js",
        "test": "jest",
        "test:watch": "jest --watch",
        "lint": "eslint src/",
        "lint:fix": "eslint src/ --fix"
      },
      "files": [
        {
          "source": "files/package.json.hbs",
          "target": "package.json",
          "type": "template",
          "description": "Package configuration"
        },
        {
          "source": "files/src/index.js.hbs",
          "target": "src/index.js",
          "type": "template",
          "description": "Main application file"
        },
        {
          "source": "files/README.md.hbs",
          "target": "README.md",
          "type": "template",
          "description": "Project documentation"
        },
        {
          "source": "config/gitignore.hbs",
          "target": ".gitignore",
          "type": "template",
          "description": "Git ignore file"
        }
      ],
      "directories": [
        {
          "path": "src",
          "description": "Source code directory"
        },
        {
          "path": "tests",
          "description": "Test files directory"
        },
        {
          "path": "docs",
          "description": "Documentation directory"
        },
        {
          "path": "scripts",
          "description": "Build and utility scripts"
        }
      ],
      "postGeneration": [
        {
          "type": "npm-install",
          "description": "Install dependencies",
          "optional": true
        },
        {
          "type": "git-init",
          "description": "Initialize Git repository",
          "optional": true
        }
      ],
      "variables": [
        {
          "name": "projectName",
          "type": "string",
          "required": true,
          "description": "Name of the project",
          "validation": "^[a-z][a-z0-9-]*$"
        },
        {
          "name": "projectDescription",
          "type": "string",
          "required": false,
          "description": "Project description"
        },
        {
          "name": "authorName",
          "type": "string",
          "required": true,
          "description": "Author name"
        },
        {
          "name": "authorEmail",
          "type": "email",
          "required": false,
          "description": "Author email"
        },
        {
          "name": "license",
          "type": "enum",
          "required": true,
          "description": "Project license",
          "options": ["MIT", "Apache-2.0", "GPL-3.0", "BSD-3-Clause"],
          "default": "MIT"
        },
        {
          "name": "port",
          "type": "number",
          "required": false,
          "description": "Server port",
          "default": 3000,
          "validation": "^([1-9][0-9]{0,3}|[1-5][0-9]{4}|6[0-4][0-9]{3}|65[0-4][0-9]{2}|655[0-2][0-9]|6553[0-5])$"
        }
      ],
      "compatibility": {
        "node": ">=14.0.0",
        "npm": ">=6.0.0",
        "platforms": ["linux", "darwin", "win32"]
      },
      "metadata": {
        "createdAt": "2024-01-01T00:00:00Z",
        "updatedAt": "2024-01-15T00:00:00Z",
        "downloadCount": 1500,
        "rating": 4.8,
        "reviews": 25
      }
    }
  ],
  "categories": [
    {
      "id": "web-framework",
      "name": "Web Frameworks",
      "description": "Templates for web application development"
    },
    {
      "id": "api",
      "name": "API Development",
      "description": "REST API and GraphQL templates"
    },
    {
      "id": "cli",
      "name": "CLI Tools",
      "description": "Command-line application templates"
    },
    {
      "id": "library",
      "name": "Libraries",
      "description": "Reusable library and package templates"
    }
  ]
}
```

### 3.2 Individual Template Definition Schema
```json
{
  "$schema": "https://codespace.dev/schemas/template.json",
  "id": "nodejs-express",
  "name": "Express.js Web Application",
  "description": "Full-featured Express.js web application",
  "version": "1.2.0",
  "template": {
    "engine": "handlebars",
    "encoding": "utf-8",
    "lineEndings": "lf",
    "indentation": {
      "type": "spaces",
      "size": 2
    }
  },
  "variables": {
    "projectName": {
      "type": "string",
      "required": true,
      "default": "{{kebabCase (prompt 'Project name')}}",
      "description": "Project name in kebab-case"
    },
    "projectDescription": {
      "type": "string",
      "required": false,
      "default": "{{prompt 'Project description'}}",
      "description": "Brief project description"
    },
    "className": {
      "type": "string",
      "required": true,
      "default": "{{pascalCase projectName}}",
      "description": "Pascal case version of project name"
    },
    "authorName": {
      "type": "string",
      "required": true,
      "default": "{{userConfig.author.name}}",
      "description": "Author's name"
    },
    "authorEmail": {
      "type": "email",
      "required": false,
      "default": "{{userConfig.author.email}}",
      "description": "Author's email"
    },
    "license": {
      "type": "enum",
      "required": true,
      "default": "{{userConfig.defaults.license}}",
      "options": ["MIT", "Apache-2.0", "GPL-3.0", "BSD-3-Clause"],
      "description": "Project license type"
    },
    "port": {
      "type": "number",
      "required": false,
      "default": 3000,
      "min": 1024,
      "max": 65535,
      "description": "Server port number"
    },
    "enableCors": {
      "type": "boolean",
      "required": false,
      "default": true,
      "description": "Enable CORS middleware"
    },
    "enableHelmet": {
      "type": "boolean",
      "required": false,
      "default": true,
      "description": "Enable Helmet security middleware"
    },
    "includeTests": {
      "type": "boolean",
      "required": false,
      "default": "{{userConfig.defaults.includeTests}}",
      "description": "Include test setup"
    },
    "includeDocs": {
      "type": "boolean",
      "required": false,
      "default": "{{userConfig.defaults.includeDocs}}",
      "description": "Include documentation setup"
    },
    "database": {
      "type": "enum",
      "required": false,
      "default": "none",
      "options": ["none", "mongodb", "postgresql", "mysql", "sqlite"],
      "description": "Database type to include"
    }
  },
  "conditionalFiles": [
    {
      "condition": "{{includeTests}}",
      "files": [
        {
          "source": "files/tests/setup.test.js.hbs",
          "target": "tests/setup.test.js"
        },
        {
          "source": "files/tests/app.test.js.hbs",
          "target": "tests/app.test.js"
        }
      ]
    },
    {
      "condition": "{{includeDocs}}",
      "files": [
        {
          "source": "files/docs/api.md.hbs",
          "target": "docs/api.md"
        },
        {
          "source": "files/docs/deployment.md.hbs",
          "target": "docs/deployment.md"
        }
      ]
    },
    {
      "condition": "{{eq database 'mongodb'}}",
      "files": [
        {
          "source": "files/config/database.js.hbs",
          "target": "config/database.js"
        },
        {
          "source": "files/models/User.js.hbs",
          "target": "models/User.js"
        }
      ]
    }
  ],
  "postProcessing": [
    {
      "type": "replace",
      "files": ["package.json"],
      "pattern": "__PROJECT_NAME__",
      "replacement": "{{projectName}}"
    },
    {
      "type": "chmod",
      "files": ["scripts/start.sh"],
      "mode": "755"
    },
    {
      "type": "npm-install",
      "condition": "{{#if userConfig.defaults.autoInstall}}true{{/if}}"
    }
  ]
}
```

## 4. Snippet Data Models

### 4.1 Snippet Index Schema
```json
{
  "$schema": "https://codespace.dev/schemas/snippet-index.json",
  "version": "1.0.0",
  "lastUpdated": "2024-01-01T00:00:00Z",
  "totalSnippets": 45,
  "categories": [
    {
      "id": "api-endpoints",
      "name": "API Endpoints",
      "description": "HTTP request handlers and routes"
    },
    {
      "id": "authentication",
      "name": "Authentication",
      "description": "Auth middleware and handlers"
    },
    {
      "id": "database",
      "name": "Database Operations",
      "description": "Database queries and models"
    },
    {
      "id": "utilities",
      "name": "Utilities",
      "description": "Helper functions and utilities"
    }
  ],
  "snippets": [
    {
      "id": "express-http-endpoint",
      "name": "Express HTTP Endpoint",
      "description": "Basic Express.js route with error handling",
      "language": "javascript",
      "framework": "express",
      "category": "api-endpoints",
      "tags": ["express", "http", "api", "endpoint", "route"],
      "filePath": "by-language/javascript/express-http-endpoint.json",
      "createdAt": "2024-01-01T00:00:00Z",
      "updatedAt": "2024-01-10T00:00:00Z",
      "usageCount": 156,
      "rating": 4.7,
      "author": "CODESPACE Team",
      "metadata": {
        "complexity": "beginner",
        "estimatedLines": 12,
        "dependencies": ["express"],
        "compatibleVersions": "^4.0.0"
      }
    },
    {
      "id": "jwt-auth-middleware",
      "name": "JWT Authentication Middleware",
      "description": "JWT verification middleware for Express",
      "language": "javascript",
      "framework": "express",
      "category": "authentication",
      "tags": ["jwt", "auth", "middleware", "security", "token"],
      "filePath": "by-language/javascript/jwt-auth-middleware.json",
      "createdAt": "2024-01-05T00:00:00Z",
      "updatedAt": "2024-01-12T00:00:00Z",
      "usageCount": 89,
      "rating": 4.9,
      "author": "CODESPACE Team",
      "metadata": {
        "complexity": "intermediate",
        "estimatedLines": 25,
        "dependencies": ["jsonwebtoken", "express"],
        "compatibleVersions": "^4.0.0"
      }
    }
  ],
  "tags": [
    {
      "name": "express",
      "count": 15,
      "category": "framework"
    },
    {
      "name": "authentication",
      "count": 8,
      "category": "feature"
    },
    {
      "name": "database",
      "count": 12,
      "category": "feature"
    },
    {
      "name": "api",
      "count": 18,
      "category": "type"
    }
  ]
}
```

### 4.2 Individual Snippet Schema
```json
{
  "$schema": "https://codespace.dev/schemas/snippet.json",
  "id": "express-http-endpoint",
  "name": "Express HTTP Endpoint",
  "description": "Basic Express.js route with proper error handling and status codes",
  "version": "1.1.0",
  "language": "javascript",
  "framework": "express",
  "category": "api-endpoints",
  "tags": ["express", "http", "api", "endpoint", "route", "error-handling"],
  "author": {
    "name": "CODESPACE Team",
    "email": "team@codespace.dev"
  },
  "metadata": {
    "complexity": "beginner",
    "estimatedLines": 12,
    "dependencies": ["express"],
    "compatibleVersions": "^4.0.0",
    "createdAt": "2024-01-01T00:00:00Z",
    "updatedAt": "2024-01-10T00:00:00Z",
    "usageCount": 156,
    "rating": 4.7,
    "reviews": 12
  },
  "code": {
    "main": "app.get('/api/endpoint', async (req, res) => {\n  try {\n    // Your logic here\n    const data = await getDataFromDatabase();\n    \n    res.status(200).json({\n      success: true,\n      data: data,\n      message: 'Data retrieved successfully'\n    });\n  } catch (error) {\n    console.error('Error in endpoint:', error);\n    res.status(500).json({\n      success: false,\n      error: 'Internal server error',\n      message: error.message\n    });\n  }\n});",
    "imports": [],
    "variables": [
      {
        "name": "endpointPath",
        "type": "string",
        "default": "/api/endpoint",
        "description": "The route path"
      },
      {
        "name": "httpMethod",
        "type": "enum",
        "default": "get",
        "options": ["get", "post", "put", "delete", "patch"],
        "description": "HTTP method"
      }
    ]
  },
  "dependencies": [
    {
      "name": "express",
      "version": "^4.0.0",
      "type": "runtime",
      "description": "Express.js framework"
    }
  ],
  "usage": {
    "description": "Add this snippet to your Express app to create a new API endpoint",
    "instructions": [
      "1. Make sure you have Express.js installed",
      "2. Replace the endpoint path with your desired route",
      "3. Implement the getDataFromDatabase function or replace with your logic",
      "4. Customize the response structure as needed"
    ],
    "examples": [
      {
        "title": "GET endpoint for users",
        "code": "app.get('/api/users', async (req, res) => { ... });"
      },
      {
        "title": "POST endpoint for creating data",
        "code": "app.post('/api/data', async (req, res) => { ... });"
      }
    ]
  },
  "templates": [
    {
      "name": "Basic endpoint",
      "code": "app.{{httpMethod}}('{{endpointPath}}', async (req, res) => {\n  try {\n    // Your logic here\n    res.status(200).json({ success: true });\n  } catch (error) {\n    res.status(500).json({ error: error.message });\n  }\n});"
    },
    {
      "name": "With database query",
      "code": "app.{{httpMethod}}('{{endpointPath}}', async (req, res) => {\n  try {\n    const data = await getDataFromDatabase();\n    res.status(200).json({ success: true, data });\n  } catch (error) {\n    res.status(500).json({ error: error.message });\n  }\n});"
    }
  ],
  "tests": [
    {
      "framework": "jest",
      "code": "describe('{{endpointPath}} endpoint', () => {\n  test('should return success response', async () => {\n    const response = await request(app)\n      .{{httpMethod}}('{{endpointPath}}')\n      .expect(200);\n    \n    expect(response.body.success).toBe(true);\n  });\n  \n  test('should handle errors gracefully', async () => {\n    // Mock error condition\n    jest.spyOn(console, 'error');\n    \n    const response = await request(app)\n      .{{httpMethod}}('{{endpointPath}}')\n      .expect(500);\n    \n    expect(response.body.success).toBe(false);\n  });\n});"
    }
  ]
}
```

## 5. Configuration Data Models

### 5.1 User Configuration Schema
```json
{
  "$schema": "https://codespace.dev/schemas/user-config.json",
  "version": "1.0.0",
  "configVersion": "1.2.0",
  "createdAt": "2024-01-01T00:00:00Z",
  "lastUpdated": "2024-01-15T00:00:00Z",
  "profile": {
    "author": {
      "name": "John Doe",
      "email": "john.doe@example.com",
      "github": "johndoe",
      "website": "https://johndoe.dev",
      "company": "Acme Corp"
    },
    "preferences": {
      "defaultLanguage": "javascript",
      "defaultLicense": "MIT",
      "defaultFramework": "express",
      "timeZone": "UTC",
      "dateFormat": "YYYY-MM-DD",
      "editor": "vscode"
    }
  },
  "defaults": {
    "projectSettings": {
      "includeTests": true,
      "includeDocs": true,
      "includeCI": true,
      "gitInit": true,
      "autoInstall": false,
      "createReadme": true,
      "createLicense": true
    },
    "ciSettings": {
      "provider": "github",
      "nodeVersion": "18",
      "enableTests": true,
      "enableLinting": true,
      "enableCoverage": false,
      "enableDeploy": false
    },
    "templateSettings": {
      "lineEndings": "lf",
      "indentType": "spaces",
      "indentSize": 2,
      "quoteType": "single",
      "trailingComma": true
    }
  },
  "paths": {
    "templatesDirectory": "~/.codespace/templates",
    "snippetsDirectory": "~/.codespace/snippets",
    "cacheDirectory": "~/.codespace/cache",
    "logsDirectory": "~/.codespace/logs",
    "backupDirectory": "~/.codespace/backups",
    "customTemplatesDirectory": "~/custom-codespace-templates"
  },
  "customTemplates": [
    {
      "id": "company-express",
      "name": "Company Express Template",
      "path": "~/custom-codespace-templates/company-express",
      "priority": 1,
      "override": false
    }
  ],
  "snippets": {
    "customSnippetsDirectory": "~/custom-codespace-snippets",
    "favoriteSnippets": [
      "express-http-endpoint",
      "jwt-auth-middleware",
      "error-handler-middleware"
    ],
    "recentSnippets": [
      "express-http-endpoint",
      "mongodb-connection"
    ],
    "maxRecentSnippets": 10
  },
  "integrations": {
    "github": {
      "enabled": true,
      "defaultBranch": "main",
      "autoCreateRepo": false,
      "privacy": "private",
      "includeGitHubActions": true
    },
    "gitlab": {
      "enabled": false,
      "autoCreateRepo": false,
      "visibility": "private"
    },
    "npm": {
      "enabled": true,
      "autoPublish": false,
      "defaultRegistry": "https://registry.npmjs.org/"
    },
    "vscode": {
      "enabled": true,
      "autoOpen": true,
      "recommendedExtensions": [
        "ms-vscode.vscode-eslint",
        "bradlc.vscode-tailwindcss",
        "ms-vscode.vscode-json"
      ]
    }
  },
  "advanced": {
    "debugMode": false,
    "verboseLogging": false,
    "telemetry": {
      "enabled": true,
      "anonymous": true,
      "dataCollection": ["usage", "errors", "performance"]
    },
    "experimentalFeatures": {
      "aiSuggestions": false,
      "collaborativeTemplates": false,
      "realTimePreview": false
    },
    "performance": {
      "cacheEnabled": true,
      "cacheTimeout": 3600000,
      "maxConcurrentOperations": 5,
      "timeoutDuration": 30000
    }
  },
  "ui": {
    "theme": "auto",
    "colorScheme": "default",
    "showProgressBars": true,
    "confirmDestructiveActions": true,
    "showHints": true,
    "compactMode": false
  },
  "backups": {
    "enabled": true,
    "frequency": "daily",
    "maxBackups": 30,
    "backupOnConfigChange": true,
    "backupPaths": [
      "config/user.json",
      "snippets/index.json",
      "templates/registry.json"
    ]
  }
}
```

### 5.2 CI/CD Presets Schema
```json
{
  "$schema": "https://codespace.dev/schemas/ci-presets.json",
  "version": "1.0.0",
  "presets": [
    {
      "id": "nodejs-standard",
      "name": "Standard Node.js CI",
      "description": "Standard CI/CD pipeline for Node.js projects",
      "language": "javascript",
      "framework": "any",
      "workflow": {
        "name": "Node.js CI",
        "on": {
          "push": {
            "branches": ["main", "develop"]
          },
          "pull_request": {
            "branches": ["main"]
          }
        },
        "jobs": {
          "test": {
            "runs-on": "ubuntu-latest",
            "strategy": {
              "matrix": {
                "node-version": [16, 18, 20]
              }
            },
            "steps": [
              {
                "uses": "actions/checkout@v4"
              },
              {
                "name": "Use Node.js ${{ matrix.node-version }}",
                "uses": "actions/setup-node@v4",
                "with": {
                  "node-version": "${{ matrix.node-version }}",
                  "cache": "npm"
                }
              },
              {
                "run": "npm ci"
              },
              {
                "run": "npm run lint"
              },
              {
                "run": "npm test"
              }
            ]
          }
        }
      }
    },
    {
      "id": "python-django",
      "name": "Django CI/CD",
      "description": "CI/CD pipeline for Django applications",
      "language": "python",
      "framework": "django",
      "workflow": {
        "name": "Django CI",
        "on": {
          "push": {
            "branches": ["main", "develop"]
          }
        },
        "jobs": {
          "test": {
            "runs-on": "ubuntu-latest",
            "services": {
              "postgres": {
                "image": "postgres:15",
                "env": {
                  "POSTGRES_PASSWORD": "postgres"
                }
              }
            },
            "steps": [
              {
                "uses": "actions/checkout@v4"
              },
              {
                "name": "Set up Python",
                "uses": "actions/setup-python@v4",
                "with": {
                  "python-version": "3.11"
                }
              },
              {
                "name": "Install dependencies",
                "run": |
                  python -m pip install --upgrade pip
                  pip install -r requirements.txt
              },
              {
                "name": "Run tests",
                "run": |
                  python manage.py test
                  python manage.py check --deploy
              }
            ]
          }
        }
      }
    }
  ]
}
```

## 6. Cache and Performance Data Models

### 6.1 Template Cache Schema
```json
{
  "$schema": "https://codespace.dev/schemas/template-cache.json",
  "version": "1.0.0",
  "createdAt": "2024-01-01T00:00:00Z",
  "expiresAt": "2024-01-02T00:00:00Z",
  "templates": {
    "nodejs-express": {
      "cachedAt": "2024-01-01T12:00:00Z",
      "lastUsed": "2024-01-01T15:30:00Z",
      "useCount": 5,
      "compiledFiles": [
        {
          "path": "package.json.hbs",
          "hash": "sha256:abc123...",
          "size": 1024
        },
        {
          "path": "src/index.js.hbs",
          "hash": "sha256:def456...",
          "size": 2048
        }
      ],
      "renderedCache": {
        "standard-variables": {
          "hash": "sha256:cached-hash",
          "renderedFiles": {
            "package.json": "{\"name\":\"{{projectName}}\"...}",
            "src/index.js": "const express = require('express')..."
          }
        }
      }
    }
  },
  "metadata": {
    "totalCacheSize": 1048576,
    "cacheHitRate": 0.85,
    "totalTemplates": 15,
    "cachedTemplates": 12
  }
}
```

### 6.2 Performance Metrics Schema
```json
{
  "$schema": "https://codespace.dev/schemas/performance-metrics.json",
  "version": "1.0.0",
  "collectionPeriod": {
    "start": "2024-01-01T00:00:00Z",
    "end": "2024-01-31T23:59:59Z"
  },
  "operations": [
    {
      "id": "op_001",
      "type": "template-generation",
      "templateId": "nodejs-express",
      "timestamp": "2024-01-15T10:30:00Z",
      "duration": 1850,
      "success": true,
      "metrics": {
        "templateLoadTime": 150,
        "renderTime": 800,
        "fileWriteTime": 600,
        "totalFiles": 8,
        "totalSize": 15360
      },
      "environment": {
        "platform": "darwin",
        "nodeVersion": "18.17.0",
        "memoryUsage": 67108864,
        "cpuUsage": 0.15
      }
    }
  ],
  "summaries": {
    "totalOperations": 156,
    "averageGenerationTime": 1250,
    "successRate": 0.98,
    "mostUsedTemplate": "nodejs-express",
    "averageProjectSize": 20480,
    "peakMemoryUsage": 134217728
  }
}
```

## 7. JSON Schemas for Validation

### 7.1 Template Schema Validation
```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "$id": "https://codespace.dev/schemas/template.json",
  "title": "CODESPACE Template Definition",
  "description": "Schema for validating CODESPACE template definitions",
  "type": "object",
  "required": ["id", "name", "description", "version", "variables", "files"],
  "properties": {
    "id": {
      "type": "string",
      "pattern": "^[a-z][a-z0-9-]*$",
      "minLength": 3,
      "maxLength": 50
    },
    "name": {
      "type": "string",
      "minLength": 5,
      "maxLength": 100
    },
    "description": {
      "type": "string",
      "minLength": 10,
      "maxLength": 500
    },
    "version": {
      "type": "string",
      "pattern": "^\\d+\\.\\d+\\.\\d+$"
    },
    "language": {
      "type": "string",
      "enum": ["javascript", "typescript", "python", "java", "go", "rust", "php"]
    },
    "framework": {
      "type": "string",
      "maxLength": 50
    },
    "variables": {
      "type": "object",
      "patternProperties": {
        "^[a-zA-Z][a-zA-Z0-9]*$": {
          "type": "object",
          "required": ["type"],
          "properties": {
            "type": {
              "type": "string",
              "enum": ["string", "number", "boolean", "email", "url", "enum", "array"]
            },
            "required": {
              "type": "boolean"
            },
            "default": {},
            "description": {
              "type": "string"
            },
            "validation": {
              "type": "string"
            },
            "options": {
              "type": "array",
              "items": {
                "type": "string"
              }
            }
          }
        }
      }
    },
    "files": {
      "type": "array",
      "items": {
        "type": "object",
        "required": ["source", "target"],
        "properties": {
          "source": {
            "type": "string"
          },
          "target": {
            "type": "string"
          },
          "type": {
            "type": "string",
            "enum": ["template", "static", "binary"]
          }
        }
      }
    }
  }
}
```

### 7.2 Snippet Schema Validation
```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "$id": "https://codespace.dev/schemas/snippet.json",
  "title": "CODESPACE Snippet Definition",
  "description": "Schema for validating CODESPACE snippet definitions",
  "type": "object",
  "required": ["id", "name", "description", "language", "code"],
  "properties": {
    "id": {
      "type": "string",
      "pattern": "^[a-z][a-z0-9-]*$",
      "minLength": 3,
      "maxLength": 50
    },
    "name": {
      "type": "string",
      "minLength": 5,
      "maxLength": 100
    },
    "description": {
      "type": "string",
      "minLength": 10,
      "maxLength": 500
    },
    "language": {
      "type": "string",
      "enum": ["javascript", "typescript", "python", "java", "go", "rust", "php", "html", "css", "sql"]
    },
    "framework": {
      "type": "string",
      "maxLength": 50
    },
    "tags": {
      "type": "array",
      "items": {
        "type": "string",
        "pattern": "^[a-z][a-z0-9-]*$"
      },
      "maxItems": 10
    },
    "code": {
      "type": "object",
      "required": ["main"],
      "properties": {
        "main": {
          "type": "string",
          "minLength": 10
        },
        "imports": {
          "type": "array",
          "items": {
            "type": "string"
          }
        },
        "variables": {
          "type": "array",
          "items": {
            "$ref": "#/definitions/variable"
          }
        }
      }
    }
  },
  "definitions": {
    "variable": {
      "type": "object",
      "required": ["name", "type"],
      "properties": {
        "name": {
          "type": "string",
          "pattern": "^[a-zA-Z][a-zA-Z0-9]*$"
        },
        "type": {
          "type": "string",
          "enum": ["string", "number", "boolean", "enum"]
        },
        "default": {},
        "description": {
          "type": "string"
        }
      }
    }
  }
}
```

## 8. Data Migration Strategies

### 8.1 Version Migration Plan
```json
{
  "migrations": [
    {
      "fromVersion": "1.0.0",
      "toVersion": "1.1.0",
      "description": "Add framework field to snippets",
      "operations": [
        {
          "type": "add-field",
          "target": "snippets[*]",
          "field": "framework",
          "default": null,
          "required": false
        }
      ]
    },
    {
      "fromVersion": "1.1.0",
      "toVersion": "1.2.0",
      "description": "Add post-processing to templates",
      "operations": [
        {
          "type": "add-field",
          "target": "templates",
          "field": "postProcessing",
          "default": [],
          "required": false
        },
        {
          "type": "rename-field",
          "target": "templates",
          "from": "afterGeneration",
          "to": "postProcessing"
        }
      ]
    }
  ]
}
```

## 9. Security and Validation Rules

### 9.1 Input Validation Rules
```json
{
  "validationRules": {
    "projectName": {
      "pattern": "^[a-z][a-z0-9-]*$",
      "minLength": 3,
      "maxLength": 50,
      "reserved": ["node_modules", "dist", "build", "vendor", "public"]
    },
    "authorEmail": {
      "pattern": "^[^@]+@[^@]+\\.[^@]+$",
      "maxLength": 100
    },
    "port": {
      "type": "number",
      "min": 1024,
      "max": 65535
    },
    "templateId": {
      "pattern": "^[a-z][a-z0-9-]*$",
      "minLength": 3,
      "maxLength": 50
    },
    "snippetId": {
      "pattern": "^[a-z][a-z0-9-]*$",
      "minLength": 3,
      "maxLength": 50
    }
  },
  "sanitization": {
    "removeHtml": true,
    "escapeSpecialChars": true,
    "validateFilePaths": true,
    "preventDirectoryTraversal": true
  }
}
```

## 10. Performance Optimization Strategies

### 10.1 Caching Strategy
```json
{
  "caching": {
    "templateCache": {
      "enabled": true,
      "ttl": 3600000,
      "maxSize": 104857600,
      "strategy": "lru"
    },
    "snippetCache": {
      "enabled": true,
      "ttl": 1800000,
      "maxSize": 52428800,
      "strategy": "lfu"
    },
    "configCache": {
      "enabled": true,
      "ttl": 300000,
      "maxSize": 1048576,
      "strategy": "simple"
    }
  },
  "optimizations": {
    "lazyLoading": true,
    "parallelProcessing": true,
    "compression": {
      "enabled": true,
      "algorithm": "gzip",
      "level": 6
    },
    "minification": {
      "enabled": false,
      "targets": ["html", "css", "js"]
    }
  }
}
```

---

This comprehensive data models and storage schema document provides the foundation for implementing a robust, scalable, and maintainable CODESPACE CLI tool with proper data validation, caching strategies, and migration support.