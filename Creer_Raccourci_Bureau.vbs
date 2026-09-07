Set WshShell = CreateObject("WScript.Shell")
Set fso = CreateObject("Scripting.FileSystemObject")

strBaseDir = fso.GetParentFolderName(WScript.ScriptFullName)
strDesktop = WshShell.SpecialFolders("Desktop")
strShortcutPath = strDesktop & "\MSK DRY PLUS - Caisse.lnk"
strTargetVbs = strBaseDir & "\Lancer_MSK_Caisse.vbs"
strIconPath = strBaseDir & "\public\favicon.ico"

Set oShortcut = WshShell.CreateShortcut(strShortcutPath)
oShortcut.TargetPath = "wscript.exe"
oShortcut.Arguments = """" & strTargetVbs & """"
oShortcut.WorkingDirectory = strBaseDir
oShortcut.WindowStyle = 1
oShortcut.Description = "MSK DRY PLUS - Logiciel de Caisse"
If fso.FileExists(strIconPath) Then
    oShortcut.IconLocation = strIconPath
End If
oShortcut.Save

MsgBox "Le raccourci 'MSK DRY PLUS - Caisse' a ete cree avec succes sur votre Bureau !", vbInformation, "MSK DRY PLUS"
