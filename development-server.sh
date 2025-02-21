#!/bin/bash
clear

# Define ambiente de desenvolvimento
export APP_ENV="development"

# Inicia o servidor PHP
php -S localhost:3000 -t .
