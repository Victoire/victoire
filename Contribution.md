# Victoire Contribution Workflow

This document describes the git workflow that should be used when contributing to Victoire.

For a more general Git-Workflow, you can have a look [here](https://github.com/asmeurer/git-workflow/blob/master/README.md)

##Clone and fork Victoire repository

**Note: The steps in this section only need to be performed once per repository**

1 : Create your own Fork

Get to [Github](https://github.com/victoire/victoire), then click on "Fork"

2 : Clone your fork on your computer

    git clone git@github.com:USER/victoire.git

3 : Acces to Victoire's original deposit :


    git remote add upstream git@github.com:Victoire/victoire.git


This command create a new remote to your deposit.
You have now two remote : one heading to your fork (origin) and another heading to the original one (upstream)
You can check this with this command


    git remote -v


That should return :


    origin  git@github.com:USER/victoire.git (fetch)
    origin  git@github.com:USER/victoire.git (push)
    upstream    git@github.com:Victoire/victoire.git (fetch)
    upstream    git@github.com:Victoire/victoire.git (push)


## Synchronize your fork with Victoire's original deposit

If this is your first set up, your fork is up to date.
If you have created your fork earlier on, there is a high probability that it doesn't include the changes made in the original deposit. In order to update it, you have to recover upstream's last commits :


    git fetch upstream

Then you need to merge them in your own fork :


    git merge upstream/masteram


## Merge your modifications

If you've worked on Victoire for a specific project, you surely have made modifications which aren't on your fork but on the Victoire's project deposit. That leads to different problematics :

* You won't be able to perform Behat tests
* If you've executed composer as --prefer-dist then Victoire's .git will be missing

The solution is to copy your modifications made in the project's Victoire in your fork.
To do so, you have to create a patch :


    cd vendor/victoire/victoire
    git diff > /tmp/patch


This operation creates a file /tmp/patch with your modifications.
You then have to create a new branch in your fork, from master :


    git checkout master
    git checkout -b feature/VIC-123-awesome-feature


And execute this command to apply the modifications :


    git apply /tmp/patch


## Integrate your modifications

After those steps, your modifications are echoed in your fork.
You can now proceed doing your commits, push and issue a Pull Request.

**Please, be descriptive in your branch names, commit messages, and pull request title and descriptions**

## Execute a Pull Request

Go to your fork's deposit on GitHub and access to the Pull Request tab : github.com/USER/victoire/pulls
You can now click on "New Pull Request" and GitHub takes in charge the next step.

*Once you have a pull request for a branch, you can push additional changes to the same branch and they will be added to the pull request automatically. You should never create a new pull request for the same branch.*

Et Voilà !

Thanks a lot for your collaboration