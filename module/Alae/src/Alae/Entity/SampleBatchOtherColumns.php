<?php

namespace Alae\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SampleBatchOtherColums
 *
 * @ORM\Table(name="alae_sample_batch_other_columns")
 * @ORM\Entity
 */
class SampleBatchOtherColumns
{

    /**
     * @var integer
     *
     * @ORM\Column(name="sample_id", type="integer", nullable=true)
     */
    protected $sampleId;

    /**
     * @var string
     *
     * @ORM\Column(name="sample_comment", type="string", length=250, nullable=true)
     */
    protected $sampleComment;

    /**
     * @var integer
     *
     * @ORM\Column(name="set_number", type="integer", nullable=true)
     */
    protected $setNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="acquisition_method", type="string", length=50, nullable=true)
     */
    protected $acquisitionMethod;

    /**
     * @var string
     *
     * @ORM\Column(name="rack_type", type="string", length=50, nullable=true)
     */
    protected $rackType;

    /**
     * @var integer
     *
     * @ORM\Column(name="rack_position", type="integer", nullable=true)
     */
    protected $rackPosition;

    /**
     * @var integer
     *
     * @ORM\Column(name="vial_position", type="integer", nullable=true)
     */
    protected $vialPosition;

    /**
     * @var string
     *
     * @ORM\Column(name="plate_type", type="string", length=50, nullable=true)
     */
    protected $plateType;

    /**
     * @var integer
     *
     * @ORM\Column(name="plate_position", type="integer", nullable=true)
     */
    protected $platePosition;

    /**
     * @var string
     *
     * @ORM\Column(name="weight_to_volume_ratio", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $weightToVolumeRatio;

    /**
     * @var string
     *
     * @ORM\Column(name="sample_annotation", type="string", length=50, nullable=true)
     */
    protected $sampleAnnotation;

    /**
     * @var string
     *
     * @ORM\Column(name="disposition", type="string", length=50, nullable=true)
     */
    protected $disposition;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_units", type="string", length=50, nullable=true)
     */
    protected $analyteUnits;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="acquisition_date", type="datetime", nullable=false)
     */
    protected $acquisitionDate;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_peak_area_for_dad", type="string", length=50, nullable=true)
     */
    protected $analytePeakAreaForDad;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_peak_height", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $analytePeakHeight;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_peak_height_for_dad", type="string", length=50, nullable=true)
     */
    protected $analytePeakHeightForDad;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_retention_time", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $analyteRetentionTime;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_expected_rt", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $analyteExpectedRt;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_rt_window", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $analyteRtWindow;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_centroid_location", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $analyteCentroidLocation;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_start_scan", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $analyteStartScan;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_start_time", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $analyteStartTime;

    /**
     * @var integer
     *
     * @ORM\Column(name="analyte_stop_scan", type="integer", nullable=true)
     */
    protected $analyteStopScan;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_stop_time", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $analyteStopTime;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_integration_type", type="string", length=50, nullable=true)
     */
    protected $analyteIntegrationType;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_signal_to_noise", type="string", length=50, nullable=true)
     */
    protected $analyteSignalToNoise;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_peak_width", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $analytePeakWidth;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_standar_query_status", type="string", length=50, nullable=true)
     */
    protected $analyteStandarQueryStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_mass_ranges", type="string", length=50, nullable=true)
     */
    protected $analyteMassRanges;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_wavelength_ranges", type="string", length=50, nullable=true)
     */
    protected $analyteWavelengthRanges;

    /**
     * @var string
     *
     * @ORM\Column(name="height_ratio", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $heightRatio;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_annotation", type="string", length=50, nullable=true)
     */
    protected $analyteAnnotation;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_channel", type="string", length=50, nullable=true)
     */
    protected $analyteChannel;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_peak_width_at_50_height", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $analytePeakWidthAt50Height;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_slope_of_baseline", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $analyteSlopeOfBaseline;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_processing_alg", type="string", length=50, nullable=true)
     */
    protected $analyteProcessingAlg;

    /**
     * @var string
     *
     * @ORM\Column(name="analyte_peak_asymmetry", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $analytePeakAsymmetry;

    /**
     * @var string
     *
     * @ORM\Column(name="is_units", type="string", length=50, nullable=true)
     */
    protected $isUnits;

    /**
     * @var string
     *
     * @ORM\Column(name="is_peak_area_for_dad", type="string", length=50, nullable=true)
     */
    protected $isPeakAreaForDad;

    /**
     * @var string
     *
     * @ORM\Column(name="is_peak_height", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $isPeakHeight;

    /**
     * @var string
     *
     * @ORM\Column(name="is_peak_height_for_dad", type="string", length=50, nullable=true)
     */
    protected $isPeakHeightForDad;

    /**
     * @var string
     *
     * @ORM\Column(name="is_concentration", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $isConcentration;

    /**
     * @var string
     *
     * @ORM\Column(name="is_retention_time", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $isRetentionTime;

    /**
     * @var string
     *
     * @ORM\Column(name="is_expected_rt", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $isExpectedRt;

    /**
     * @var string
     *
     * @ORM\Column(name="is_rt_windows", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $isRtWindows;

    /**
     * @var string
     *
     * @ORM\Column(name="is_centroid_location", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $isCentroidLocation;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_start_scan", type="integer", nullable=true)
     */
    protected $isStartScan;

    /**
     * @var string
     *
     * @ORM\Column(name="is_start_time", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $isStartTime;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_stop_scan", type="integer", nullable=true)
     */
    protected $isStopScan;

    /**
     * @var string
     *
     * @ORM\Column(name="is_stop_time", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $isStopTime;

    /**
     * @var string
     *
     * @ORM\Column(name="is_integration_type", type="string", length=50, nullable=true)
     */
    protected $isIntegrationType;

    /**
     * @var string
     *
     * @ORM\Column(name="is_signal_to_noise", type="string", length=50, nullable=true)
     */
    protected $isSignalToNoise;

    /**
     * @var string
     *
     * @ORM\Column(name="is_peak_width", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $isPeakWidth;

    /**
     * @var string
     *
     * @ORM\Column(name="is_mass_ranges", type="string", length=50, nullable=true)
     */
    protected $isMassRanges;

    /**
     * @var string
     *
     * @ORM\Column(name="is_wavelength_ranges", type="string", length=50, nullable=true)
     */
    protected $isWavelengthRanges;

    /**
     * @var string
     *
     * @ORM\Column(name="is_channel", type="string", length=50, nullable=true)
     */
    protected $isChannel;

    /**
     * @var string
     *
     * @ORM\Column(name="is_peak_width_al_50_height", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $isPeakWidthAl50Height;

    /**
     * @var string
     *
     * @ORM\Column(name="is_slope_of_baseline", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $isSlopeOfBaseline;

    /**
     * @var string
     *
     * @ORM\Column(name="is_processing_alg", type="string", length=50, nullable=true)
     */
    protected $isProcessingAlg;

    /**
     * @var string
     *
     * @ORM\Column(name="is_peak_asymemtry", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $isPeakAsymemtry;

    /**
     * @var integer
     *
     * @ORM\Column(name="record_modified", type="integer", nullable=true)
     */
    protected $recordModified;

    /**
     * @var string
     *
     * @ORM\Column(name="area_ratio", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $areaRatio;

    /**
     * @var string
     *
     * @ORM\Column(name="calculated_concentration_for_dad", type="string", length=50, nullable=true)
     */
    protected $calculatedConcentrationForDad;

    /**
     * @var string
     *
     * @ORM\Column(name="relative_retention_time", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $relativeRetentionTime;

    /**
     * @var string
     *
     * @ORM\Column(name="response_factor", type="decimal", precision=19, scale=4, nullable=true)
     */
    protected $responseFactor;

    /**
     * @var \Alae\Entity\SampleBatch
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Alae\Entity\SampleBatch")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fk_sample_batch", referencedColumnName="pk_sample_batch")
     * })
     */
    protected $fkSampleBatch;

    public function getSampleId()
    {
        return $this->sampleId;
    }

    public function setSampleId($sampleId)
    {
        $this->sampleId = $sampleId;
    }

    public function getSampleComment()
    {
        return $this->sampleComment;
    }

    public function setSampleComment($sampleComment)
    {
        $this->sampleComment = $sampleComment;
    }

    public function getSetNumber()
    {
        return $this->setNumber;
    }

    public function setSetNumber($setNumber)
    {
        $this->setNumber = $setNumber;
    }

    public function getAcquisitionMethod()
    {
        return $this->acquisitionMethod;
    }

    public function setAcquisitionMethod($acquisitionMethod)
    {
        $this->acquisitionMethod = $acquisitionMethod;
    }

    public function getRackType()
    {
        return $this->rackType;
    }

    public function setRackType($rackType)
    {
        $this->rackType = $rackType;
    }

    public function getRackPosition()
    {
        return $this->rackPosition;
    }

    public function setRackPosition($rackPosition)
    {
        $this->rackPosition = $rackPosition;
    }

    public function getVialPosition()
    {
        return $this->vialPosition;
    }

    public function setVialPosition($vialPosition)
    {
        $this->vialPosition = $vialPosition;
    }

    public function getPlateType()
    {
        return $this->plateType;
    }

    public function setPlateType($plateType)
    {
        $this->plateType = $plateType;
    }

    public function getPlatePosition()
    {
        return $this->platePosition;
    }

    public function setPlatePosition($platePosition)
    {
        $this->platePosition = $platePosition;
    }

    public function getWeightToVolumeRatio()
    {
        return $this->weightToVolumeRatio;
    }

    public function setWeightToVolumeRatio($weightToVolumeRatio)
    {
        $this->weightToVolumeRatio = $weightToVolumeRatio;
    }

    public function getSampleAnnotation()
    {
        return $this->sampleAnnotation;
    }

    public function setSampleAnnotation($sampleAnnotation)
    {
        $this->sampleAnnotation = $sampleAnnotation;
    }

    public function getDisposition()
    {
        return $this->disposition;
    }

    public function setDisposition($disposition)
    {
        $this->disposition = $disposition;
    }

    public function getAnalyteUnits()
    {
        return $this->analyteUnits;
    }

    public function setAnalyteUnits($analyteUnits)
    {
        $this->analyteUnits = $analyteUnits;
    }

    public function getAcquisitionDate()
    {
        return $this->acquisitionDate;
    }

    public function setAcquisitionDate($acquisitionDate)
    {
        $date = new \DateTime($acquisitionDate);
        $this->acquisitionDate = $date;
    }

    public function getAnalytePeakAreaForDad()
    {
        return $this->analytePeakAreaForDad;
    }

    public function setAnalytePeakAreaForDad($analytePeakAreaForDad)
    {
        $this->analytePeakAreaForDad = $analytePeakAreaForDad;
    }

    public function getAnalytePeakHeight()
    {
        return $this->analytePeakHeight;
    }

    public function setAnalytePeakHeight($analytePeakHeight)
    {
        $this->analytePeakHeight = $analytePeakHeight;
    }

    public function getAnalytePeakHeightForDad()
    {
        return $this->analytePeakHeightForDad;
    }

    public function setAnalytePeakHeightForDad($analytePeakHeightForDad)
    {
        $this->analytePeakHeightForDad = $analytePeakHeightForDad;
    }

    public function getAnalyteRetentionTime()
    {
        return $this->analyteRetentionTime;
    }

    public function setAnalyteRetentionTime($analyteRetentionTime)
    {
        $this->analyteRetentionTime = $analyteRetentionTime;
    }

    public function getAnalyteExpectedRt()
    {
        return $this->analyteExpectedRt;
    }

    public function setAnalyteExpectedRt($analyteExpectedRt)
    {
        $this->analyteExpectedRt = $analyteExpectedRt;
    }

    public function getAnalyteRtWindow()
    {
        return $this->analyteRtWindow;
    }

    public function setAnalyteRtWindow($analyteRtWindow)
    {
        $this->analyteRtWindow = $analyteRtWindow;
    }

    public function getAnalyteCentroidLocation()
    {
        return $this->analyteCentroidLocation;
    }

    public function setAnalyteCentroidLocation($analyteCentroidLocation)
    {
        $this->analyteCentroidLocation = $analyteCentroidLocation;
    }

    public function getAnalyteStartScan()
    {
        return $this->analyteStartScan;
    }

    public function setAnalyteStartScan($analyteStartScan)
    {
        $this->analyteStartScan = $analyteStartScan;
    }

    public function getAnalyteStartTime()
    {
        return $this->analyteStartTime;
    }

    public function setAnalyteStartTime($analyteStartTime)
    {
        $this->analyteStartTime = $analyteStartTime;
    }

    public function getAnalyteStopScan()
    {
        return $this->analyteStopScan;
    }

    public function setAnalyteStopScan($analyteStopScan)
    {
        $this->analyteStopScan = $analyteStopScan;
    }

    public function getAnalyteStopTime()
    {
        return $this->analyteStopTime;
    }

    public function setAnalyteStopTime($analyteStopTime)
    {
        $this->analyteStopTime = $analyteStopTime;
    }

    public function getAnalyteIntegrationType()
    {
        return $this->analyteIntegrationType;
    }

    public function setAnalyteIntegrationType($analyteIntegrationType)
    {
        $this->analyteIntegrationType = $analyteIntegrationType;
    }

    public function getAnalyteSignalToNoise()
    {
        return $this->analyteSignalToNoise;
    }

    public function setAnalyteSignalToNoise($analyteSignalToNoise)
    {
        $this->analyteSignalToNoise = $analyteSignalToNoise;
    }

    public function getAnalytePeakWidth()
    {
        return $this->analytePeakWidth;
    }

    public function setAnalytePeakWidth($analytePeakWidth)
    {
        $this->analytePeakWidth = $analytePeakWidth;
    }

    public function getAnalyteStandarQueryStatus()
    {
        return $this->analyteStandarQueryStatus;
    }

    public function setAnalyteStandarQueryStatus($analyteStandarQueryStatus)
    {
        $this->analyteStandarQueryStatus = $analyteStandarQueryStatus;
    }

    public function getAnalyteMassRanges()
    {
        return $this->analyteMassRanges;
    }

    public function setAnalyteMassRanges($analyteMassRanges)
    {
        $this->analyteMassRanges = $analyteMassRanges;
    }

    public function getAnalyteWavelengthRanges()
    {
        return $this->analyteWavelengthRanges;
    }

    public function setAnalyteWavelengthRanges($analyteWavelengthRanges)
    {
        $this->analyteWavelengthRanges = $analyteWavelengthRanges;
    }

    public function getHeightRatio()
    {
        return $this->heightRatio;
    }

    public function setHeightRatio($heightRatio)
    {
        $this->heightRatio = $heightRatio;
    }

    public function getAnalyteAnnotation()
    {
        return $this->analyteAnnotation;
    }

    public function setAnalyteAnnotation($analyteAnnotation)
    {
        $this->analyteAnnotation = $analyteAnnotation;
    }

    public function getAnalyteChannel()
    {
        return $this->analyteChannel;
    }

    public function setAnalyteChannel($analyteChannel)
    {
        $this->analyteChannel = $analyteChannel;
    }

    public function getAnalytePeakWidthAt50Height()
    {
        return $this->analytePeakWidthAt50Height;
    }

    public function setAnalytePeakWidthAt50Height($analytePeakWidthAt50Height)
    {
        $this->analytePeakWidthAt50Height = $analytePeakWidthAt50Height;
    }

    public function getAnalyteSlopeOfBaseline()
    {
        return $this->analyteSlopeOfBaseline;
    }

    public function setAnalyteSlopeOfBaseline($analyteSlopeOfBaseline)
    {
        $this->analyteSlopeOfBaseline = $analyteSlopeOfBaseline;
    }

    public function getAnalyteProcessingAlg()
    {
        return $this->analyteProcessingAlg;
    }

    public function setAnalyteProcessingAlg($analyteProcessingAlg)
    {
        $this->analyteProcessingAlg = $analyteProcessingAlg;
    }

    public function getAnalytePeakAsymmetry()
    {
        return $this->analytePeakAsymmetry;
    }

    public function setAnalytePeakAsymmetry($analytePeakAsymmetry)
    {
        $this->analytePeakAsymmetry = $analytePeakAsymmetry;
    }

    public function getIsUnits()
    {
        return $this->isUnits;
    }

    public function setIsUnits($isUnits)
    {
        $this->isUnits = $isUnits;
    }

    public function getIsPeakAreaForDad()
    {
        return $this->isPeakAreaForDad;
    }

    public function setIsPeakAreaForDad($isPeakAreaForDad)
    {
        $this->isPeakAreaForDad = $isPeakAreaForDad;
    }

    public function getIsPeakHeight()
    {
        return $this->isPeakHeight;
    }

    public function setIsPeakHeight($isPeakHeight)
    {
        $this->isPeakHeight = $isPeakHeight;
    }

    public function getIsPeakHeightForDad()
    {
        return $this->isPeakHeightForDad;
    }

    public function setIsPeakHeightForDad($isPeakHeightForDad)
    {
        $this->isPeakHeightForDad = $isPeakHeightForDad;
    }

    public function getIsConcentration()
    {
        return $this->isConcentration;
    }

    public function setIsConcentration($isConcentration)
    {
        $this->isConcentration = $isConcentration;
    }

    public function getIsRetentionTime()
    {
        return $this->isRetentionTime;
    }

    public function setIsRetentionTime($isRetentionTime)
    {
        $this->isRetentionTime = $isRetentionTime;
    }

    public function getIsExpectedRt()
    {
        return $this->isExpectedRt;
    }

    public function setIsExpectedRt($isExpectedRt)
    {
        $this->isExpectedRt = $isExpectedRt;
    }

    public function getIsRtWindows()
    {
        return $this->isRtWindows;
    }

    public function setIsRtWindows($isRtWindows)
    {
        $this->isRtWindows = $isRtWindows;
    }

    public function getIsCentroidLocation()
    {
        return $this->isCentroidLocation;
    }

    public function setIsCentroidLocation($isCentroidLocation)
    {
        $this->isCentroidLocation = $isCentroidLocation;
    }

    public function getIsStartScan()
    {
        return $this->isStartScan;
    }

    public function setIsStartScan($isStartScan)
    {
        $this->isStartScan = $isStartScan;
    }

    public function getIsStartTime()
    {
        return $this->isStartTime;
    }

    public function setIsStartTime($isStartTime)
    {
        $this->isStartTime = $isStartTime;
    }

    public function getIsStopScan()
    {
        return $this->isStopScan;
    }

    public function setIsStopScan($isStopScan)
    {
        $this->isStopScan = $isStopScan;
    }

    public function getIsStopTime()
    {
        return $this->isStopTime;
    }

    public function setIsStopTime($isStopTime)
    {
        $this->isStopTime = $isStopTime;
    }

    public function getIsIntegrationType()
    {
        return $this->isIntegrationType;
    }

    public function setIsIntegrationType($isIntegrationType)
    {
        $this->isIntegrationType = $isIntegrationType;
    }

    public function getIsSignalToNoise()
    {
        return $this->isSignalToNoise;
    }

    public function setIsSignalToNoise($isSignalToNoise)
    {
        $this->isSignalToNoise = $isSignalToNoise;
    }

    public function getIsPeakWidth()
    {
        return $this->isPeakWidth;
    }

    public function setIsPeakWidth($isPeakWidth)
    {
        $this->isPeakWidth = $isPeakWidth;
    }

    public function getIsMassRanges()
    {
        return $this->isMassRanges;
    }

    public function setIsMassRanges($isMassRanges)
    {
        $this->isMassRanges = $isMassRanges;
    }

    public function getIsWavelengthRanges()
    {
        return $this->isWavelengthRanges;
    }

    public function setIsWavelengthRanges($isWavelengthRanges)
    {
        $this->isWavelengthRanges = $isWavelengthRanges;
    }

    public function getIsChannel()
    {
        return $this->isChannel;
    }

    public function setIsChannel($isChannel)
    {
        $this->isChannel = $isChannel;
    }

    public function getIsPeakWidthAl50Height()
    {
        return $this->isPeakWidthAl50Height;
    }

    public function setIsPeakWidthAl50Height($isPeakWidthAl50Height)
    {
        $this->isPeakWidthAl50Height = $isPeakWidthAl50Height;
    }

    public function getIsSlopeOfBaseline()
    {
        return $this->isSlopeOfBaseline;
    }

    public function setIsSlopeOfBaseline($isSlopeOfBaseline)
    {
        $this->isSlopeOfBaseline = $isSlopeOfBaseline;
    }

    public function getIsProcessingAlg()
    {
        return $this->isProcessingAlg;
    }

    public function setIsProcessingAlg($isProcessingAlg)
    {
        $this->isProcessingAlg = $isProcessingAlg;
    }

    public function getIsPeakAsymemtry()
    {
        return $this->isPeakAsymemtry;
    }

    public function setIsPeakAsymemtry($isPeakAsymemtry)
    {
        $this->isPeakAsymemtry = $isPeakAsymemtry;
    }

    public function getRecordModified()
    {
        return $this->recordModified;
    }

    public function setRecordModified($recordModified)
    {
        $this->recordModified = $recordModified;
    }

    public function getAreaRatio()
    {
        return $this->areaRatio;
    }

    public function setAreaRatio($areaRatio)
    {
        $this->areaRatio = $areaRatio;
    }

    public function getCalculatedConcentrationForDad()
    {
        return $this->calculatedConcentrationForDad;
    }

    public function setCalculatedConcentrationForDad($calculatedConcentrationForDad)
    {
        $this->calculatedConcentrationForDad = $calculatedConcentrationForDad;
    }

    public function getRelativeRetentionTime()
    {
        return $this->relativeRetentionTime;
    }

    public function setRelativeRetentionTime($relativeRetentionTime)
    {
        $this->relativeRetentionTime = $relativeRetentionTime;
    }

    public function getResponseFactor()
    {
        return $this->responseFactor;
    }

    public function setResponseFactor($responseFactor)
    {
        $this->responseFactor = $responseFactor;
    }

    public function getFkSampleBatch()
    {
        return $this->fkSampleBatch;
    }

    public function setFkSampleBatch(\Alae\Entity\SampleBatch $fkSampleBatch)
    {
        $this->fkSampleBatch = $fkSampleBatch;
    }

}
