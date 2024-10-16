<?php

namespace App\Service;

use stdClass;
use DateTime;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Erpag\DatabaseBundle\Wrapper\DbWrapper;
use App\Service\UncService;
use App\Service\DocumentService;

class AppService
{
    /**
     * @var string
     */
    private $bormEvtDir;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var DbWrapper
     */
    private $db;

    /**
     * @var UncService
     */
    private $us;

    /**
     * @var DocumentService
     */
    private $ds;
    
    public function __construct(string $bormEvtDir, RequestStack $requestStack, ParameterBagInterface $params, DbWrapper $db, UncService $us)
    {
        $this->bormEvtDir = $bormEvtDir;
        $this->requestStack = $requestStack;
        $this->params = $params;
        $this->db = $db;
        $this->us = $us;
    }

    public function createContact(stdClass $contact):string
    {
        $timestamp = (new DateTime())->format('U');
        $filename = $this->getUniqueDirectory($timestamp);

        $directoryCreated = true;

        $directoryCreated = $this->createDirectory($filename);

        //Nur wenn keine Dateien hochgeladen wurden, oder der Dateiupload erfolgreich war, wird weitergemacht
        if($directoryCreated == true)
        {
            $this->createFile([
                'TYPE' => 'CONTACT',
                'DIRECTORY' => $filename,
                'ADDRESS_ID' => $contact->addressId,
                'COMPANY_NAME' => $contact->companyName,
                'COMPANY_MAIL' => $contact->companyMail,
                'COMPANY_TEL' => $contact->companyTel,
                'COMPANY_STREET' => $contact->companyStreet,
                'COMPANY_PLZ' => $contact->companyPlz,
                'COMPANY_PLACE' => $contact->companyPlace,
                'COMPANY_COUNTRY' => $contact->companyCountry,
                'COMPANY_OTHER_COUNTRY' => $contact->companyOtherCountry,
                'CONTACT_SELECT_ID' => $contact->contactSelect,
                'CONTACT_NAME' => $contact->contactName,
                'CONTACT_FUNCTION' => $contact->contactFunction,
                'CONTACT_TEL' => $contact->contactTel,
                'CONTACT_MAIL' => $contact->contactMail,
                'NOTE' => $contact->note,
                'USERNAME' => $contact->username,
            ], $this->us->getUNCPath($filename.'\\'.'contact'));
            return $filename;
        }
        else
        {
            return '';
        }
    }

    public function getPriceFromConfiguration($grundmodul, $kaffeemaschine, $verlaengerungLinks, $verlaengerungRechts, $verkleidungLinks, $verkleidungRechts, $verkleidungHinten, $revisionstuerLinks, $revisionstuerRechts, $revisionstuerHinten, $directory)
    {
        $gesamtPreis = 0.0;

        $grundmodulGekuehltPrice = 66150.0;
        $grundmodulUngekuehltPrice = 60300.0;

        $grundmodulMontagekosten = 6500.0;
        $grundmodulVerkleidung = 10731.0;
        $grundmodulFront = 9968.0;
        $fahrtkostenPkw = 550.0;
        $fahrtkostenLkw = 1050.0;
        $reisezeitMonteure = 1344.0;
        $reisezeitTechniker = 582.0;
        $speditionskosten = 1500.0;
        $einweisungRoboter = 1474.74;

        $kaffeemaschinePrice = 16500.0 + 5850.0;
        $verlaengerungPrice = 3631.0 + 1225.0;
        $verkleidungPrice = 5106.0;
        $revisionstuerPrice = 652.0;
        
        $grundModulKomplett = $grundmodulMontagekosten + $grundmodulVerkleidung + $grundmodulFront + $fahrtkostenPkw + $fahrtkostenLkw + $reisezeitMonteure + $reisezeitTechniker + $speditionskosten + $einweisungRoboter;

        $array = [];
        if($grundmodul == 'Gekuehlt')
        {
            $gesamtPreis += $grundmodulGekuehltPrice + $grundModulKomplett;
            $obj = new stdClass();
            $obj->designation = 'Grundmodul gekühlt';
            $obj->price = $grundmodulGekuehltPrice + $grundModulKomplett;
            array_push($array, $obj);
        }
        else
        {
            $gesamtPreis += $grundmodulUngekuehltPrice + $grundModulKomplett;
            $obj = new stdClass();
            $obj->designation = 'Grundmodul ungekühlt';
            $obj->price = $grundmodulUngekuehltPrice + $grundModulKomplett;
            array_push($array, $obj);
        }

        if($kaffeemaschine == 'Ja')
        {
            $gesamtPreis += $kaffeemaschinePrice;
            $obj = new stdClass();
            $obj->designation = 'Kaffeemaschine';
            $obj->price = $kaffeemaschinePrice;
            array_push($array, $obj);
        }

        if($verlaengerungLinks == 'Ja')
        {
            $gesamtPreis += $verlaengerungPrice;
            $obj = new stdClass();
            $obj->designation = 'Verlängerung links';
            $obj->price = $verlaengerungPrice;
            array_push($array, $obj);
        }

        if($verlaengerungRechts == 'Ja')
        {
            $gesamtPreis += $verlaengerungPrice;
            $obj = new stdClass();
            $obj->designation = 'Verlängerung rechts';
            $obj->price = $verlaengerungPrice;
            array_push($array, $obj);
        }

        if($verkleidungLinks == 'Ja')
        {
            $gesamtPreis += $verkleidungPrice;
            $obj = new stdClass();
            $obj->designation = 'Verkleidung links';
            $obj->price = $verkleidungPrice;
            array_push($array, $obj);
        }

        if($verkleidungRechts == 'Ja')
        {
            $gesamtPreis += $verkleidungPrice;
            $obj = new stdClass();
            $obj->designation = 'Verkleidung rechts';
            $obj->price = $verkleidungPrice;
            array_push($array, $obj);
        }

        if($verkleidungHinten == 'Ja')
        {
            $gesamtPreis += $verkleidungPrice;
            $obj = new stdClass();
            $obj->designation = 'Verkleidung hinten';
            $obj->price = $verkleidungPrice;
            array_push($array, $obj);
        }

        if($revisionstuerLinks == 'Ja')
        {
            $gesamtPreis += $revisionstuerPrice;
            $obj = new stdClass();
            $obj->designation = 'Revisionstür links';
            $obj->price = $revisionstuerPrice;
            array_push($array, $obj);
        }

        if($revisionstuerRechts == 'Ja')
        {
            $gesamtPreis += $revisionstuerPrice;
            $obj = new stdClass();
            $obj->designation = 'Revisionstür rechts';
            $obj->price = $revisionstuerPrice;
            array_push($array, $obj);
        }

        if($revisionstuerHinten == 'Ja')
        {
            $gesamtPreis += $revisionstuerPrice;
            $obj = new stdClass();
            $obj->designation = 'Revisionstür hinten';
            $obj->price = $revisionstuerPrice;
            array_push($array, $obj);
        }

        $obj = new stdClass();
        $obj->designation = 'Gesamt';
        $obj->price = $gesamtPreis;
        array_push($array, $obj);

        //Datei mit der Konfiguration ablegen
        $this->createFile([
            'TYPE' => 'KONFIGURATION',
            'DIRECTORY' => $directory,
            'GRUNDMODUL' => $grundmodul,
            'KAFFEEMASCHINE' => $kaffeemaschine,
            'VERLAENGERUNG_LINKS' => $verlaengerungLinks,
            'VERLAENGERUNG_RECHTS' => $verlaengerungRechts,
            'VERKLEIDUNG_LINKS' => $verkleidungLinks,
            'VERKLEIDUNG_RECHTS' => $verkleidungRechts,
            'VERKLEIDUNG_HINTEN' => $verkleidungHinten,
            'REVISIONSTUER_LINKS' => $revisionstuerLinks,
            'REVISIONSTUER_RECHTS' => $revisionstuerRechts,
            'REVISIONSTUER_HINTEN' => $revisionstuerHinten,
        ], $this->us->getUNCPath($directory.'\\'.'konfiguration'));

        return $array;
    }

    private function getUniqueDirectory($timestamp)
    {
        $dir = $this->us->getUNCPath($this->bormEvtDir);
        $dirname = $dir.''.$timestamp;

        $renameCounter = 1;
        $newDirname = basename($dirname);

        //Ordner
        while (file_exists($this->us->getUNCPath(dirname($dirname).'\\'.$newDirname))) {
            $newDirname = str_replace('.'.pathinfo($fidirnamele, PATHINFO_EXTENSION), '', basename($dirname))
                .'('.$renameCounter.')'
            ;
            $renameCounter++;
        }

        return $newDirname;
    }

    private function getUniqueFilename($filename)
    {
        $newFilename = $filename;
        $renameCounter = 1;
        //Datei
        while (file_exists($this->us->getUNCPath($newFilename))) {
            $newFilename = str_replace('.'.pathinfo($filename, PATHINFO_EXTENSION), '', basename($filename))
                .'('.$renameCounter.').'
                .pathinfo($filename, PATHINFO_EXTENSION)
                ;
            $renameCounter++;
            $newFilename = $this->us->getUNCPath(dirname($filename).'\\'.$newFilename);
        }

        return $newFilename;
    }

    private function createDirectory($dirname):bool
    {
        $dir = $this->us->getUNCPath($this->bormEvtDir);
        $targetDir = $dir.$dirname;

        if (!is_dir($this->us->getUNCPath($targetDir))) {
            mkdir($this->us->getUNCPath($targetDir), 0777, true);
        }

        return true;
    }

    private function createFile($data, $filename)
    {
        $dir = $this->us->getUNCPath($this->bormEvtDir);
        $targetDir = $dir.$filename;
        $targetFile = $targetDir.'.txt';

        $targetFile = $this->getUniqueFilename($targetFile);

        $txt = "[DATA]".PHP_EOL;
        
        foreach($data as $key => $value)
        {
            $txt .= $key.'='.$value.PHP_EOL;
        }
        
        file_put_contents($targetFile, $txt);
    }

    public function getContactFromDirectory($directory, $searchTerm)
    {
        $file = $this->us->getUNCPath($this->bormEvtDir.'\\'.$directory.'\\'.'contact.txt');

        // Datei einlesen und die Zeilen in einem Array speichern
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Durch die Zeilen gehen und nach einem bestimmten Eintrag suchen
        $found = null;

        foreach ($lines as $line) {
            // Prüfen, ob die Zeile den gesuchten Eintrag enthält
            if (strpos($line, $searchTerm) !== false) {
                // Die Zeile enthält den gesuchten Eintrag, diesen Wert extrahieren
                // $found = trim($line);  // Leerzeichen entfernen
                $found = trim(explode('=', $line)[1]);
                break;  // Beenden, da der Eintrag gefunden wurde
            }

            // if (strpos($line, $searchKey . '=') === 0) {
            //     // Schlüssel und Wert trennen
            //     list($key, $value) = explode('=', $line, 2);
            //     $foundValue = trim($value); // Leerzeichen entfernen
            //     break; // Beenden, da der Eintrag gefunden wurde
            // }
        }

        // Ergebnis anzeigen
        if ($found) {
            return $found;
        } else {
            return 'Kunde';
        }
    }
}