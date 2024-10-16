<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\SecurityBundle\Security;
use Erpag\DBQueryServiceBundle\Service\QueryService;
use App\Service\AppService;
use stdClass;

class AppController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function indexAction(): Response
    {
        return $this->render('page.html.twig', [
        ]);
    }

    #[Route('/adressen', name: 'adressen')]
    public function adressenCollectionAction(Request $request, QueryService $qs): Response
    {
        // $meldungen = [];
        $meldungenFehler = $request->getSession()->get('X-ERROR');
        $meldungenErfolg = $request->getSession()->get('X-ERFOLG');
        $request->getSession()->remove('X-ERROR');
        $request->getSession()->remove('X-ERFOLG');

        $filter = $request->get('filter', '');

        if ($filter == '')
        {
            $query_addresses = $qs
                ->identifiedBy('Address', 'default')
                ->replace([
                ])
                ->withPagination($request->get('page', 1), 40);
        }
        else
        {
            $query_addresses = $qs
                ->identifiedBy('Address', 'search')
                ->replace([
                    'FILTER' => '%' . $filter . '%',
                ])
                ->withPagination($request->get('page', 1), 40);
        }

        $_query = $query_addresses->getQuery();

        $addresses = $query_addresses->getResult() ?? [];

        return $this->render('adressen.html.twig', [
            'addresses' => $addresses,
            'filter' => $filter,
            'pagination_count' =>$_query->count,
            'pagination_limit' =>$_query->limit,
        ]);
    }

    #[Route('/contact/{addressId}', name: 'contact')]
    public function contactFormularAction(Request $request, QueryService $qs, AppService $as, $addressId): Response
    {
        $address = $qs
            ->new()
            ->identifiedBy('Address', 'by_id')
            ->replace([
                'ID' => $addressId
            ])
            ->getResult()[0] ?? []
        ;

        $inputContact = $request->get('contact') ?? 0;
        $inputCompanyName = $request->get('companyName') ?? '';
        $inputCompanyTel = $request->get('companyTel') ?? '';
        $inputCompanyMail = $request->get('companyMail') ?? '';
        $inputCompanyStreet = $request->get('companyStreet') ?? '';
        $inputCompanyPlz = $request->get('companyPlz') ?? '';
        $inputCompanyPlace = $request->get('companyPlace') ?? '';
        $inputCompanyCountry = $request->get('companyCountry') ?? '';
        $inputCompanyOtherCountry = $request->get('companyOtherCountry') ?? '';
        $inputContactSelect = $request->get('contactSelect') ?? 0;
        $inputContactName = $request->get('contactName') ?? '';
        $inputContactFunction = $request->get('contactFunction') ?? '';
        $inputContactTel = $request->get('contactTel') ?? '';
        $inputContactMail = $request->get('contactMail') ?? '';
        $inputNote = $request->get('note') ?? '';

        //Wenn der Hochladen-Button gedrückt wurde
        if($request->get('sent')){

            $obj = new stdClass();
            $obj->addressId = $addressId;
            $obj->companyName = $inputCompanyName;
            $obj->companyTel = $inputCompanyTel;
            $obj->companyMail = $inputCompanyMail;
            $obj->companyStreet = $inputCompanyStreet;
            $obj->companyPlz = $inputCompanyPlz;
            $obj->companyPlace = $inputCompanyPlace;
            $obj->companyCountry = $inputCompanyCountry;
            $obj->companyOtherCountry = $inputCompanyOtherCountry;
            $obj->contactSelect = $inputContactSelect;
            $obj->contactName = $inputContactName;
            $obj->contactFunction = $inputContactFunction;
            $obj->contactTel = $inputContactTel;
            $obj->contactMail = $inputContactMail;
            $obj->note = $inputNote;
            $obj->username = strtoupper($this->getUser()->getUsername());

            $directory = $as->createContact($obj);

            if($directory != '')
            {
                return $this->redirectToRoute('konfiguration', [
                    'addressId' => $addressId,
                    'directory' => $directory,
                ]);
            }
        }

        $contacts = $qs
            ->new()
            ->identifiedBy('Contact', 'by_address')
            ->replace([
                'ADDRESS_ID' => $addressId,
                ])
            ->getResult() ?? []
        ;

        $countries = $qs
            ->new()
            ->identifiedBy('Address', 'countries')
            ->replace([
            ])
            ->getResult() ?? []
        ;

        return $this->render('contact.html.twig', [
            'addressId' => $addressId,
            'inputContact' => $inputContact,
            'inputCompanyName' => $inputCompanyName,
            'inputCompanyTel' => $inputCompanyTel,
            'inputCompanyMail' => $inputCompanyMail,
            'inputCompanyStreet' => $inputCompanyStreet,
            'inputCompanyPlz' => $inputCompanyPlz,
            'inputCompanyPlace' => $inputCompanyPlace,
            'inputCompanyCountry' => $inputCompanyCountry,
            'inputCompanyOtherCountry' => $inputCompanyOtherCountry,
            'inputContactSelect' => $inputContactSelect,
            'inputContactName' => $inputContactName,
            'inputContactFunction' => $inputContactFunction,
            'inputContactTel' => $inputContactTel,
            'inputContactMail' => $inputContactMail,
            'inputNote' => $inputNote,
            'address' => $address,
            'contacts' => $contacts,
            'countries' => $countries,
        ]);
    }

    #[Route('/konfiguration/{addressId}/{directory}', name: 'konfiguration')]
    public function backjeKonfigurationAction(Request $request, QueryService $qs, AppService $as, $addressId, $directory): Response
    {
        //AdressId == 0, dann Datei auslesen
        //Ansonsten AdrName nutzen

        if($addressId == 0)
        {
            $adrName = $as->getContactFromDirectory($directory, 'COMPANY_NAME');
            $adrOrt = $as->getContactFromDirectory($directory, 'COMPANY_PLACE');
        }
        else
        {
            $address = $qs
                ->identifiedBy('Address', 'by_id')
                ->replace([
                    'ID' => $addressId
                ])
                ->getResult()[0] ?? []
            ;
            
            $adrName = $address->adrName;
            $adrOrt = $address->ort;
        }

        $inputAusfuehrungGrundmodul = $request->get('ausfuehrungGrundmodul') ?? '';
        $inputKaffeemaschine = $request->get('kaffeemaschine') ?? '';
        $inputVerlaengerungLinks = $request->get('verlaengerungLinks') ?? '';
        $inputVerlaengerungRechts = $request->get('verlaengerungRechts') ?? '';
        $inputVerkleidungLinks = $request->get('verkleidungLinks') ?? '';
        $inputVerkleidungRechts = $request->get('verkleidungRechts') ?? '';
        $inputVerkleidungHinten = $request->get('verkleidungHinten') ?? '';
        $inputTuerLinks = $request->get('tuerLinks') ?? '';
        $inputTuerRechts = $request->get('tuerRechts') ?? '';
        $inputTuerHinten = $request->get('tuerHinten') ?? '';

        // //Wenn der Hochladen-Button gedrückt wurde
        // if($request->get('sent'))
        // {
        //     $priceArray = $as->getPriceFromConfiguration(
        //                         $inputAusfuehrungGrundmodul,
        //                         $inputKaffeemaschine,
        //                         $inputVerlaengerungLinks,
        //                         $inputVerlaengerungRechts,
        //                         $inputVerkleidungLinks,
        //                         $inputVerkleidungRechts,
        //                         $inputVerkleidungHinten,
        //                         $inputTuerLinks,
        //                         $inputTuerRechts,
        //                         $inputTuerHinten,
        //                         $directory,
        //                     );

        //     // return $this->redirectToRoute('zusammenfassung', [
        //     return $this->forward('App\Controller\AppController::backjeZusammenfassungAction', [
        //         'addressId' => $addressId,
        //         'directory' => $directory,
        //         'priceArray' => $priceArray,
        //     ]);
        // }

        return $this->render('konfiguration.html.twig', [
            'addressId' => $addressId,
            'adrName' => $adrName,
            'adrOrt' => $adrOrt,
            'inputAusfuehrungGrundmodul' => $inputAusfuehrungGrundmodul,
            'inputKaffeemaschine' => $inputKaffeemaschine,
            'inputVerlaengerungLinks' => $inputVerlaengerungLinks,
            'inputVerlaengerungRechts' => $inputVerlaengerungRechts,
            'inputVerkleidungLinks' => $inputVerkleidungLinks,
            'inputVerkleidungRechts' => $inputVerkleidungRechts,
            'inputVerkleidungHinten' => $inputVerkleidungHinten,
            'inputTuerLinks' => $inputTuerLinks,
            'inputTuerRechts' => $inputTuerRechts,
            'inputTuerHinten' => $inputTuerHinten,
            'directory' => $directory,
        ]);
    }

    #[Route('/zusammenfassung/{addressId}/{directory}', name: 'zusammenfassung')]
    public function backjeZusammenfassungAction(Request $request, QueryService $qs, AppService $as, $addressId, $directory): Response
    {
        if($addressId == 0)
        {
            $adrName = $as->getContactFromDirectory($directory, 'COMPANY_NAME');
            $adrOrt = $as->getContactFromDirectory($directory, 'COMPANY_PLACE');
        }
        else
        {
            $address = $qs
                ->identifiedBy('Address', 'by_id')
                ->replace([
                    'ID' => $addressId
                ])
                ->getResult()[0] ?? []
            ;
            
            $adrName = $address->adrName;
            $adrOrt = $address->ort;
        }

        $inputAusfuehrungGrundmodul = $request->get('ausfuehrungGrundmodul') ?? '';
        $inputKaffeemaschine = $request->get('kaffeemaschine') ?? '';
        $inputVerlaengerungLinks = $request->get('verlaengerungLinks') ?? '';
        $inputVerlaengerungRechts = $request->get('verlaengerungRechts') ?? '';
        $inputVerkleidungLinks = $request->get('verkleidungLinks') ?? '';
        $inputVerkleidungRechts = $request->get('verkleidungRechts') ?? '';
        $inputVerkleidungHinten = $request->get('verkleidungHinten') ?? '';
        $inputTuerLinks = $request->get('tuerLinks') ?? '';
        $inputTuerRechts = $request->get('tuerRechts') ?? '';
        $inputTuerHinten = $request->get('tuerHinten') ?? '';

        $priceArray = $as->getPriceFromConfiguration(
                            $inputAusfuehrungGrundmodul,
                            $inputKaffeemaschine,
                            $inputVerlaengerungLinks,
                            $inputVerlaengerungRechts,
                            $inputVerkleidungLinks,
                            $inputVerkleidungRechts,
                            $inputVerkleidungHinten,
                            $inputTuerLinks,
                            $inputTuerRechts,
                            $inputTuerHinten,
                            $directory,
                        );

                        $gesamtpreis = end($priceArray)->price;

        return $this->render('zusammenfassung.html.twig', [
            'addressId' => $addressId,
            'adrName' => $adrName,
            'adrOrt' => $adrOrt,
            'directory' => $directory,
            'priceArray' => $priceArray,
            'gesamtpreis' => $gesamtpreis,
        ]);
    }


    #[Route('/amortisierung', name: 'amortisierung')]
    public function backjeAmortisierungAction(Request $request): Response
    {
        $inputGesamtsumme = $request->get('gesamtsumme') ?? '93999.74';
        $inputArbeitszeit = $request->get('arbeitszeit') ?? '37.5';
        $inputWochen = $request->get('wochen') ?? '43';
        $inputStundenleistung = $request->get('stundenleistung') ?? '76.00';
        $inputTageProMonat = $request->get('tageProMonat') ?? '30';
        $inputUmsatzProTag = (($inputArbeitszeit*$inputWochen*$inputStundenleistung)/12)/$inputTageProMonat;
        $inputUmsatzProJahr = $inputArbeitszeit*$inputWochen*$inputStundenleistung;
        $inputAnteilPersAnUmsatz = $request->get('anteilPersAnUmsatz') ?? '30';
        $inputPersaufwand = $inputUmsatzProJahr*($inputAnteilPersAnUmsatz/100);
        $inputDurchschnittsbon = $request->get('durchschnittsbon') ?? '4.00';
        $inputKundenanzahl = round($inputUmsatzProTag / $inputDurchschnittsbon, 0);
        $inputAmortisierung = round($inputGesamtsumme / $inputPersaufwand, 2);
        // $inputVerlaengerungLinks = $request->get('verlaengerungLinks') ?? '';
        // $inputVerlaengerungRechts = $request->get('verlaengerungRechts') ?? '';
        // $inputVerkleidungLinks = $request->get('verkleidungLinks') ?? '';
        // $inputVerkleidungRechts = $request->get('verkleidungRechts') ?? '';
        // $inputVerkleidungHinten = $request->get('verkleidungHinten') ?? '';
        // $inputTuerLinks = $request->get('tuerLinks') ?? '';
        // $inputTuerRechts = $request->get('tuerRechts') ?? '';
        // $inputTuerHinten = $request->get('tuerHinten') ?? '';

        // //Wenn der Hochladen-Button gedrückt wurde
        // if($request->get('sent'))
        // {
        //     $priceArray = $as->getPriceFromConfiguration(
        //                         $inputAusfuehrungGrundmodul,
        //                         $inputKaffeemaschine,
        //                         $inputVerlaengerungLinks,
        //                         $inputVerlaengerungRechts,
        //                         $inputVerkleidungLinks,
        //                         $inputVerkleidungRechts,
        //                         $inputVerkleidungHinten,
        //                         $inputTuerLinks,
        //                         $inputTuerRechts,
        //                         $inputTuerHinten,
        //                         $directory,
        //                     );

        //     // return $this->redirectToRoute('zusammenfassung', [
        //     return $this->forward('App\Controller\AppController::backjeZusammenfassungAction', [
        //         'addressId' => $addressId,
        //         'directory' => $directory,
        //         'priceArray' => $priceArray,
        //     ]);
        // }

        return $this->render('amortisierung.html.twig', [
            // 'addressId' => $addressId,
            // 'adrName' => $adrName,
            // 'adrOrt' => $adrOrt,
            'inputGesamtsumme' => $inputGesamtsumme,
            'inputArbeitszeit' => $inputArbeitszeit,
            'inputWochen' => $inputWochen,
            'inputStundenleistung' => $inputStundenleistung,
            'inputTageProMonat' => $inputTageProMonat,
            'inputUmsatzProTag' => $inputUmsatzProTag,
            'inputUmsatzProJahr' => $inputUmsatzProJahr,
            'inputAnteilPersAnUmsatz' => $inputAnteilPersAnUmsatz,
            'inputPersaufwand' => $inputPersaufwand,
            'inputDurchschnittsbon' => $inputDurchschnittsbon,
            'inputKundenanzahl' => $inputKundenanzahl,
            'inputAmortisierung' => $inputAmortisierung,
            // 'inputVerlaengerungRechts' => $inputVerlaengerungRechts,
            // 'inputVerkleidungLinks' => $inputVerkleidungLinks,
            // 'inputVerkleidungRechts' => $inputVerkleidungRechts,
            // 'inputVerkleidungHinten' => $inputVerkleidungHinten,
            // 'inputTuerLinks' => $inputTuerLinks,
            // 'inputTuerRechts' => $inputTuerRechts,
            // 'inputTuerHinten' => $inputTuerHinten,
            // 'directory' => $directory,
        ]);
    }
}