#!/bin/bash

echo "=========================================="
echo "Create GitHub Repository"
echo "=========================================="
echo ""

# Check if token is provided
if [ -z "$1" ]; then
    echo "Usage: ./create_github_repo.sh YOUR_GITHUB_TOKEN"
    echo ""
    echo "Get your token from: https://github.com/settings/tokens"
    echo "Required scopes: repo (full control of private repositories)"
    echo ""
    exit 1
fi

TOKEN=$1
REPO_NAME="masinicusut"
DESCRIPTION="E-commerce site pentru piese mașini de cusut - PHP, MySQL, Shopping Cart, Admin Panel"

echo "Creating repository: $REPO_NAME"
echo ""

# Create repository using GitHub API
RESPONSE=$(curl -X POST \
  -H "Authorization: token $TOKEN" \
  -H "Accept: application/vnd.github.v3+json" \
  https://api.github.com/user/repos \
  -d "{
    \"name\": \"$REPO_NAME\",
    \"description\": \"$DESCRIPTION\",
    \"private\": false,
    \"auto_init\": false
  }")

# Check if repository was created
if echo "$RESPONSE" | grep -q "clone_url"; then
    CLONE_URL=$(echo "$RESPONSE" | grep -o '"clone_url": "[^"]*' | cut -d'"' -f4)
    HTML_URL=$(echo "$RESPONSE" | grep -o '"html_url": "[^"]*' | cut -d'"' -f4)

    echo "✓ Repository created successfully!"
    echo ""
    echo "Repository URL: $HTML_URL"
    echo ""

    # Add remote and push
    echo "Adding remote origin..."
    git remote add origin "$CLONE_URL" 2>/dev/null || git remote set-url origin "$CLONE_URL"

    echo "Pushing to GitHub..."
    git push -u origin main

    echo ""
    echo "=========================================="
    echo "✓ Done! Repository is on GitHub"
    echo "=========================================="
    echo ""
    echo "View repository: $HTML_URL"
    echo ""
else
    echo "✗ Error creating repository"
    echo "$RESPONSE"
    exit 1
fi
