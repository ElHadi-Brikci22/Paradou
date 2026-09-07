Set WshShell = CreateObject("WScript.Shell")
Set fso = CreateObject("Scripting.FileSystemObject")

strBaseDir = fso.GetParentFolderName(WScript.ScriptFullName)
strStartup = WshShell.SpecialFolders("Startup")
strShortcutPath = strStartup & "\MSK_Dry_Plus_AutoStart.lnk"
strTargetVbs = strBaseDir & "\Lancer_MSK_Caisse.vbs"
strIconPath = strBaseDir & "\public\favicon.ico"

intAnswer = MsgBox("Voulez-vous que l'application MSK Caisse se lance automatiquement a l'allumage de l'ordinateur ?", vbYesNo + vbQuestion, "MSK DRY PLUS - Demarrage Automatique")

If intAnswer = vbYes Then
    Set oShortcut = WshShell.CreateShortcut(strShortcutPath)
    oShortcut.TargetPath = "wscript.exe"
    oShortcut.Arguments = """" & strTargetVbs & """"
    oShortcut.WorkingDirectory = strBaseDir
    oShortcut.WindowStyle = 1
    oShortcut.Description = "MSK DRY PLUS - Demarrage automatique"
    If fso.FileExists(strIconPath) Then
        oShortcut.IconLocation = strIconPath
    End If
    oShortcut.Save
    MsgBox "Le demarrage automatique a ete active avec succes !", vbInformation, "MSK DRY PLUS"
Else
    If fso.FileExists(strShortcutPath) Then
        fso.DeleteFile strShortcutPath, True
        MsgBox "Le demarrage automatique a ete desactive.", vbInformation, "MSK DRY PLUS"
    Else
        MsgBox "Aucune modification effectuee.", vbInformation, "MSK DRY PLUS"
    End If
End If
