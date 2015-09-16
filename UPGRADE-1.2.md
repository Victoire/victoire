#UPGRADE 1.2.0

- Replace in your project the following terms :


|order | OLD  | NEW  |
|---|---|---|
|\#1| BusinessEntityPagePattern  | BusinessTemplate  |
|\#2| BusinessEntityPage  | BusinessPage  |

- Run these sql queries:

```
UPDATE `vic_view` SET type = 'businesspage' WHERE type = 'businessentitypage';
UPDATE `vic_view` SET type = 'businesstemplate' WHERE type = 'businessentitypagepattern';
```
