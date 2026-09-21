<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\GarmentItem;

return new class extends Migration
{
    public function up(): void
    {
        $rules = [
            // Costumes & Ensembles
            ['pattern' => '/costume.*3.*p|ensemble.*3.*p/i', 'file' => '02-Costume 3ps.jpg'],
            ['pattern' => '/costume.*2.*p|costume.*2.*pcs|costume.*2ps/i', 'file' => '01-Costume-2PS.jpg'],
            ['pattern' => '/costume.*enfant/i', 'file' => '03-Costume -12.jpg'],
            ['pattern' => '/tailleur|ensemble.*femme/i', 'file' => 'Ensemble Tailleur.jpg'],
            ['pattern' => '/costume|smoking/i', 'file' => '01-Costume-2PS.jpg'],
            ['pattern' => '/ensemble.*travail|combinaison/i', 'file' => '26-Combinaison.jpg'],
            ['pattern' => '/ensemble.*traditionnel/i', 'file' => '25-Jabador 2PS.jpg'],
            ['pattern' => '/ensemble.*bebe|tenue.*ceremonie|uniforme.*complet.*bebe/i', 'file' => 'Grenouillère bébé.jpg'],
            ['pattern' => '/ensemble/i', 'file' => 'ensemble 3 ps.jpg'],

            // Chemises & Cravates
            ['pattern' => '/chemise.*12|chemise.*enfant/i', 'file' => 'Chemise -12ANS.jpg'],
            ['pattern' => '/chemisier/i', 'file' => '27-Chemisier.jpg'],
            ['pattern' => '/chemise.*nuit/i', 'file' => '34-Chemise de nuit.jpg'],
            ['pattern' => '/chemise/i', 'file' => '05-Chemise.jpg'],
            ['pattern' => '/cravate|noeud.*papillon/iu', 'file' => '05-Cravate.jpg'],

            // Cuir & Daim & Fourrure
            ['pattern' => '/fourrure/i', 'file' => 'Manteau fourrure.jpg'],
            ['pattern' => '/cuir/i', 'file' => '13-Jacket simili cuir.jpg'],
            ['pattern' => '/daim/i', 'file' => 'Blouson daim.jpg'],
            ['pattern' => '/nubuk/i', 'file' => 'Blouson nubuk.jpg'],
            ['pattern' => '/vachette/i', 'file' => 'Blouson vachette.jpg'],
            ['pattern' => '/textile.*special/i', 'file' => '13-Jacket simili cuir.jpg'],

            // Vestes & Blazers
            ['pattern' => '/veste.*survet/i', 'file' => '09-Veste survette.jpg'],
            ['pattern' => '/veste.*karakou/i', 'file' => '16-Veste karakou.jpg'],
            ['pattern' => '/veste.*police/i', 'file' => 'Veste police.jpg'],
            ['pattern' => '/veste/i', 'file' => '03-Veste.jpg'],
            ['pattern' => '/blazer/i', 'file' => '03-Veste.jpg'],
            ['pattern' => '/blouson/i', 'file' => 'Blouzon .jpg'],
            ['pattern' => '/bomber|jacket/i', 'file' => '12-Jacket.jpg'],
            ['pattern' => '/gilet.*doudoune/i', 'file' => '16-Gilet doudoune.jpg'],
            ['pattern' => '/gilet/i', 'file' => '04-Gilet.jpg'],
            ['pattern' => '/cardigan|gardigan/i', 'file' => '18-Gardigan.jpg'],

            // Manteaux & Doudounes
            ['pattern' => '/doudoune.*12|doudoune.*enfant/i', 'file' => '02-Doudoune -12.jpg'],
            ['pattern' => '/doudoune.*femme/i', 'file' => '07-Doudoune femme.jpg'],
            ['pattern' => '/doudoune/i', 'file' => '10-Doudoune.jpg'],
            ['pattern' => '/anorak.*enfant/i', 'file' => '01-Anorak enfant.jpg'],
            ['pattern' => '/anorak.*femme/i', 'file' => '06-Anorak femme.jpg'],
            ['pattern' => '/anorak/i', 'file' => '11-Anorak.jpg'],
            ['pattern' => '/impermeable.*femme/iu', 'file' => '06-Imperméable femme.jpg'],
            ['pattern' => '/impermeable/iu', 'file' => '13-Imperméable.jpg'],
            ['pattern' => '/trench|pardessus/i', 'file' => '03-Manteau long.jpg'],
            ['pattern' => '/manteau.*long|manteau.*hom.*lon/i', 'file' => '13-Manteau hom lon.jpg'],
            ['pattern' => '/manteau.*demi|manteau.*dem.*lon/i', 'file' => '14-Manteau dem lon.jpg'],
            ['pattern' => '/manteau.*court/i', 'file' => '15-Manteau court.jpg'],
            ['pattern' => '/manteau/i', 'file' => 'Manteau.jpg'],
            ['pattern' => '/parka|coupe.*vent/i', 'file' => '11-Anorak.jpg'],
            ['pattern' => '/polaire/i', 'file' => '19-Pule.jpg'],

            // Pantalons & Jeans & Shorts
            ['pattern' => '/jean.*12|jean.*enfant/i', 'file' => 'Jeans -12.jpg'],
            ['pattern' => '/jean/i', 'file' => '07-JEANS.jpg'],
            ['pattern' => '/pantalon.*survet.*12/i', 'file' => 'Pontalon survet -12.jpg'],
            ['pattern' => '/pantalon.*toile.*12/i', 'file' => 'Pontalon toile -12.jpg'],
            ['pattern' => '/pantalon.*survet|jogging/i', 'file' => '09-Pantalon survette.jpg'],
            ['pattern' => '/pantalon.*toile/i', 'file' => '08-Pantalon toile.jpg'],
            ['pattern' => '/pantalon.*classique/i', 'file' => '06-Pantalon classique.jpg'],
            ['pattern' => '/pantalon.*femme|legging/i', 'file' => '29-pantalon femme.jpg'],
            ['pattern' => '/pantalon|pantacourt/i', 'file' => 'Pantalon.jpg'],
            ['pattern' => '/short|bermuda/i', 'file' => '21-Shorts.jpg'],
            ['pattern' => '/survet/i', 'file' => '09-Survette.jpg'],
            ['pattern' => '/salopette|salopete/i', 'file' => '25-Salopette.jpg'],
            ['pattern' => '/pyjama|pijama/i', 'file' => '26-Pijamas.jpg'],

            // Hauts / T-Shirts / Pulls
            ['pattern' => '/polo/i', 'file' => '20-POLO.jpg'],
            ['pattern' => '/t.*shirt|debardeur/iu', 'file' => '20-POLO.jpg'],
            ['pattern' => '/sous.*pull|col.*roule/iu', 'file' => '19-Pule.jpg'],
            ['pattern' => '/pull.*femme/i', 'file' => '28-Pull femme.jpg'],
            ['pattern' => '/pull|pule/i', 'file' => 'Pull.jpg'],
            ['pattern' => '/sweat.*fermeture|sweat.*capuche|sweat/i', 'file' => 'sweet a fermeuture.jpg'],
            ['pattern' => '/top|blouse/i', 'file' => '27-Chemisier.jpg'],

            // Robes & Jupes
            ['pattern' => '/robe.*mariee/iu', 'file' => '24-Robe de mariée.jpg'],
            ['pattern' => '/robe.*soire/iu', 'file' => '23-Robe soire.jpg'],
            ['pattern' => '/robe.*longue/i', 'file' => '21-Robes longues.jpg'],
            ['pattern' => '/robe.*courte/i', 'file' => '20-Robe courte.jpg'],
            ['pattern' => '/robe.*bapteme/iu', 'file' => 'Robe de bapteme.jpg'],
            ['pattern' => '/robe.*avocat/i', 'file' => '30-Robe avocat.jpg'],
            ['pattern' => '/robe.*mousseline|robe.*mousline/i', 'file' => '22-Robe mousline.jpg'],
            ['pattern' => '/robe|abaya/i', 'file' => 'ROBE.jpg'],
            ['pattern' => '/jupe.*plissee/iu', 'file' => '19-Jupe plissée.jpg'],
            ['pattern' => '/jupe/i', 'file' => '18-Jupes.jpg'],

            // Traditionnel
            ['pattern' => '/caftan.*double/i', 'file' => '11-Caftan double.jpg'],
            ['pattern' => '/caftan|takchita/i', 'file' => '10-Caftan.jpg'],
            ['pattern' => '/blouza.*mansouj/i', 'file' => '13-Blouza mansouj.jpg'],
            ['pattern' => '/blouza/i', 'file' => '14-Blouza.jpg'],
            ['pattern' => '/sarouel.*karakou|karakou.*sarouel/i', 'file' => '17-Sarouel-karakou.jpg'],
            ['pattern' => '/karakou.*pantalon|karakou.*jupe|karakou/i', 'file' => '15-Ensemble karakou.jpg'],
            ['pattern' => '/qamis/i', 'file' => '23-Qamis.jpg'],
            ['pattern' => '/gandoura/i', 'file' => '24-Gandoura.jpg'],
            ['pattern' => '/jabador/i', 'file' => '25-Jabador 2PS.jpg'],
            ['pattern' => '/jalaba.*homme|djellaba.*homme|burnous|qachabia|kachabia/i', 'file' => '22-Jalaba homme.jpg'],
            ['pattern' => '/jalaba|djellaba/i', 'file' => '01-Jalaba femme.jpg'],
            ['pattern' => '/foukia|fouqiya/i', 'file' => '12-Foukia & tahtia.jpg'],
            ['pattern' => '/khalidjia/i', 'file' => 'KHALIDJIA.jpg'],
            ['pattern' => '/jebba|bed3ia/i', 'file' => '02-Bed3ia.jpg'],
            ['pattern' => '/hijab/i', 'file' => '33-Hijab.jpg'],
            ['pattern' => '/voile|malaya/i', 'file' => '32-Voile.jpg'],
            ['pattern' => '/chal|chale/iu', 'file' => '31-chal.jpg'],
            ['pattern' => '/mandil|mendil|m.*herma/i', 'file' => 'mandil.jpg'],
            ['pattern' => '/fouta/i', 'file' => '13-FOTA MANSOUDJ.jpg'],

            // Accessoires
            ['pattern' => '/casquette.*police|caskette.*police/i', 'file' => 'Caskette police.jpg'],
            ['pattern' => '/casquette|caskette/i', 'file' => 'Caskette.jpg'],
            ['pattern' => '/chapeau/i', 'file' => 'Chapeau.jpg'],
            ['pattern' => '/ceinture|cinture|hzem|mdamma/i', 'file' => 'CINTURE.jpg'],
            ['pattern' => '/sac.*dos/i', 'file' => '21-Sac a dos.jpg'],
            ['pattern' => '/chaussure|espadrie/i', 'file' => 'Espadries.jpg'],
            ['pattern' => '/foulard|echarpe|cheche|khimar|haik|etole|tahwika|abrouk/iu', 'file' => '31-chal.jpg'],
            ['pattern' => '/gants|moufles/i', 'file' => 'CINTURE.jpg'],
            ['pattern' => '/bonnet/i', 'file' => 'Caskette.jpg'],

            // Uniformes
            ['pattern' => '/uniforme.*douane/i', 'file' => '27-Uniforme Douane.jpg'],
            ['pattern' => '/uniforme.*gendarmerie/i', 'file' => '28-Uniforme gendarmerie.jpg'],
            ['pattern' => '/uniforme.*police/i', 'file' => '29-Uniforme police.jpg'],
            ['pattern' => '/tablier/i', 'file' => 'Tablier.jpg'],

            // Bébé
            ['pattern' => '/grenouillere|dors.*bien|body|barboteuse/iu', 'file' => 'Grenouillère bébé.jpg'],
            ['pattern' => '/londeau|landau/i', 'file' => '22-Londeau bebe.jpg'],
            ['pattern' => '/couverture.*bebe/iu', 'file' => '05-Couverture bébé.jpg'],
            ['pattern' => '/bavoir|chaussette|chausson/i', 'file' => 'Grenouillère bébé.jpg'],

            // Linge de maison - Lit
            ['pattern' => '/couette.*1/i', 'file' => '01-Couette 1p.jpg'],
            ['pattern' => '/couette.*2|couette/i', 'file' => '02-Couette 2p.jpg'],
            ['pattern' => '/couvrelit|dessus.*de.*lit|couvre.*lit/i', 'file' => '02-couvrelit.jpg'],
            ['pattern' => '/couverture.*1/i', 'file' => '03-Couverture 1p.jpg'],
            ['pattern' => '/couverture.*2|couverture/i', 'file' => '04-Couverture 2p.jpg'],
            ['pattern' => '/drap.*housse.*1|drap.*houe.*1/i', 'file' => '08-Drap-houe-1p.jpg'],
            ['pattern' => '/drap.*housse.*2|drap.*houe.*2/i', 'file' => '09-Drap-houe-2p.jpg'],
            ['pattern' => '/drap.*1.*sans.*rep|drap.*1.*son.*rep/i', 'file' => 'Drap 1p son rep.jpg'],
            ['pattern' => '/drap.*2.*sans.*rep|drap.*2.*son.*rep/i', 'file' => 'Drap 2p son rep.jpg'],
            ['pattern' => '/drap.*1/i', 'file' => '06-Drap 1p.jpg'],
            ['pattern' => '/drap.*2|drap/i', 'file' => '07-Drap 2p.jpg'],
            ['pattern' => '/housse.*drap.*1/i', 'file' => 'Housse drap 1p sr.jpg'],
            ['pattern' => '/housse.*drap.*2|housse.*couette/i', 'file' => 'Housse drap 2p sr.jpg'],
            ['pattern' => '/taie.*oreill/i', 'file' => "Taie d'oreillet g.jpg"],
            ['pattern' => '/taie.*traversin|housse.*traversin/i', 'file' => "Taie d'oreillet g.jpg"],
            ['pattern' => '/descente.*lit|decent.*lit/i', 'file' => '20-Decent de lit 3P.jpg'],
            ['pattern' => '/coussin.*matelas.*1|housse.*matelas/i', 'file' => '10-Coussin matelas 1PL.jpg'],
            ['pattern' => '/coussin.*matelas.*2/i', 'file' => '11-Coussin pour Matelas 2P.jpg'],
            ['pattern' => '/oreiller|traversin/i', 'file' => '13-Taie oreillet.jpg'],
            ['pattern' => '/protege.*matelas|alese|surmatelas|plaid/iu', 'file' => '02-couvrelit.jpg'],
            ['pattern' => '/edredon/iu', 'file' => '02-Couette 2p.jpg'],

            // Bain & Table
            ['pattern' => '/serviette.*bain|drap.*bain/i', 'file' => 'Serviette de bain.jpg'],
            ['pattern' => '/serviette/i', 'file' => 'serviette.jpg'],
            ['pattern' => '/sortie.*bain|peignoir/i', 'file' => '30-Sortie de bain.jpg'],
            ['pattern' => '/gant.*toilette/i', 'file' => 'serviette.jpg'],
            ['pattern' => '/nappe.*4/i', 'file' => '14-nappe 4place.jpg'],
            ['pattern' => '/nappe.*6|nappe/i', 'file' => '15-Nappe 6place.jpg'],
            ['pattern' => '/chemin.*table|set.*table/i', 'file' => '14-nappe 4place.jpg'],

            // Rideaux & Housses
            ['pattern' => '/rideau|voilage|store|brise.*bise|valance/i', 'file' => '16-rideau.jpg'],
            ['pattern' => '/salon.*marocain/i', 'file' => '12-Salon marocain.jpg'],
            ['pattern' => '/housse.*chaise/i', 'file' => 'Housse chaise.jpg'],
            ['pattern' => '/housse.*canape|housse.*fauteuil|housse.*coussin|housse.*protection|jete.*canape|coussin/iu', 'file' => 'Housse chaise.jpg'],
            ['pattern' => '/housse.*voiture|moquette.*auto|ciel.*toit|housse.*volant|housse.*coffre/i', 'file' => 'Housse voiture.jpg'],
            ['pattern' => '/housse.*moto|selle.*moto|housse.*selle/i', 'file' => 'Housse moto.jpg'],
            ['pattern' => '/housse.*quad|housse.*buggy/i', 'file' => 'Housse quad.jpg'],
            ['pattern' => '/housse.*velo/iu', 'file' => 'Housse velo.jpg'],
            ['pattern' => '/bateau|bimini|taud|capote.*bateau|sellerie.*bateau|housse.*console/i', 'file' => 'Housse voiture.jpg'],
            ['pattern' => '/valise|sacoche|tente|bache|sac.*couchage/iu', 'file' => '21-Sac a dos.jpg'],
            ['pattern' => '/tapis|paillasson/i', 'file' => 'tapis.jpg'],

            // Fallback
            ['pattern' => '/.*/', 'file' => '03-Veste.jpg'],
        ];

        $items = GarmentItem::whereNull('image_path')->orWhere('image_path', '')->get();

        foreach ($items as $item) {
            $name = $item->name;
            // Clean accent representation
            $normName = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
            $normName = str_replace(["'", '"'], ' ', $normName);

            $assignedFile = '03-Veste.jpg';
            foreach ($rules as $rule) {
                if (preg_match($rule['pattern'], $name) || preg_match($rule['pattern'], $normName)) {
                    $assignedFile = $rule['file'];
                    break;
                }
            }

            $item->image_path = 'images/catalog/' . $assignedFile;
            $item->save();
        }
    }

    public function down(): void
    {
        // Safe down: no action needed
    }
};
