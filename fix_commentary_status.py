#!/usr/bin/env python3
"""
Fix commentary_status CHECK constraint in soccer_dads_postgres.sql.
Adds '' (empty string) to the list of allowed values so rows with an
empty commentary_status don't violate the constraint on import.
"""
import re
import sys

path = '/home/forge/soccer_dads_postgres.sql'

with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# Matches: CHECK ("commentary_status" IN ('val1', 'val2', ...))
# Group 1: opening   CHECK ("commentary_status" IN (
# Group 2: value list
# Group 3: closing   ))
pattern = r"""(CHECK \("commentary_status" IN \()([^)]+)(\)\))"""
match = re.search(pattern, content)

if not match:
    print("ERROR: CHECK constraint for commentary_status not found.", file=sys.stderr)
    sys.exit(1)

old = match.group(0)
replacement = f"{match.group(1)}{match.group(2)}, ''{match.group(3)}"

print(f"Found:   {old}")
print(f"Updated: {replacement}")

content = content[:match.start()] + replacement + content[match.end():]

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

print("File saved.")
