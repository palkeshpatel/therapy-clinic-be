import re

file_path = '/home/ganesha/.gemini/antigravity-ide/brain/20b32a6c-4ca3-43ad-b1fd-93c59795b61c/task.md'
with open(file_path, 'r') as f:
    content = f.read()

content = content.replace('- `[ ]` Add `reports.service.ts` functions', '- `[x]` Add `reports.service.ts` functions')

with open(file_path, 'w') as f:
    f.write(content)
