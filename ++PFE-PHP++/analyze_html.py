from bs4 import BeautifulSoup

with open(r'C:\Users\wassi\OneDrive\Desktop\pfeeeee\admin\Light\FR-white\dashboard.html', 'r', encoding='utf-8') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')
body = soup.body

print('Immediate children of body:')
for child in body.find_all(recursive=False):
    if child.name:
        print(f'<{child.name} class="{child.get("class", [])}" id="{child.get("id", "")}">')
